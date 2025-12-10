<?php

namespace App\Repositories;

use App\Models\Transaction;
use Illuminate\Support\Collection;
use App\Banking\Transactions\Strategies\DepositStrategy;
use App\Banking\Transactions\Strategies\TransactionStrategy;
use App\Banking\Transactions\Strategies\TransferStrategy;
use App\Banking\Transactions\Strategies\WithdrawStrategy;
use App\Events\TransactionRejected;
use App\Jobs\LogJob;
use App\Models\Account;
use App\Models\Notification;
use App\Models\SchedualeTransaction;
use DomainException;
use Illuminate\Support\Facades\Auth;

class EloquentTransactionRepository implements TransactionRepositoryInterface
{

  public function find(int $id): ?Transaction
  {
    return Transaction::find($id);
  }

  public function approve(int $id)
  {
    $user = Auth::user();
    $transaction = Transaction::with('account')->findOrFail($id);
    $account = $transaction->account;

    if ($user->role_id != $transaction->role_id) {
      return [
        'status' => false,
        'message' => 'Unauthorized to do this job'
      ];
    }

    $strategy = $this->strategyFor($transaction->type);
    $status = $strategy->executeFromTransaction($transaction, $account);
    if ($status == false) {
      $transaction->update(['status' => 'failed']);
      Notification::query()->create([
        'user_id' => $account->customer_id,
        'content' => "Failed to withdraw {$transaction['amount']} because your balance is not enough",
        'type' => 'Failed Approvment'
      ]);
      return [
        'status' => false,
        'message' => 'Transaction Failed',
      ];
    }

    LogJob::dispatch(
      $user->id,
      $transaction->type,
      $transaction->type == 'invoice'
        ? "The invoice for {$transaction->amount} was accepted by {$user->name}"
        : "User {$user['name']} approved transaction {$transaction['id']}"
    );

    return [
      'status' => true,
      'message' => 'Transaction approved successfully',
      'transaction' => $transaction->fresh()
    ];
  }

  public function reject(int $id)
  {
    $user = Auth::user();
    $transaction = Transaction::with('account')->findOrFail($id);

    if ($user->role_id != $transaction->role_id) {
      return [
        'status' => false,
        'message' => 'Unauthorized to do this job'
      ];
    }

    if ($transaction->status != 'pending') {
      return [
        'status' => false,
        'message' => 'Something went wrong'
      ];
    }

    $transaction->update([
      'status' => 'rejected'
    ]);

    $transaction['user_id'] = $transaction->account->customer->id;
    event(new TransactionRejected($transaction));
    return [
      'success' => true,
      'message' => 'Transaction rejected successfully'
    ];
  }

  public function createSchedule(array $data)
  {
    $data['account_id'] = Account::where('number', $data['account_id'])->first()->id;
    $data['account_related_id'] = Account::where('number', $data['account_related_id'])->first()->id ?? null;
    return SchedualeTransaction::create($data);
  }

  public function showTransactions()
  {
    $user = Auth::user();
    return Transaction::query()->where('role_id', $user->role_id)->where('status', 'pending')->get();
  }

  public function strategyFor(string $type): TransactionStrategy
  {
    return match ($type) {
      'deposit' => new DepositStrategy(),
      'withdraw' => new WithdrawStrategy(),
      'transfer' => new TransferStrategy(),
      'invoice' => new TransferStrategy(),
      default => throw new DomainException('Unsupported transaction type')
    };
  }

  public function lastNByAccount(int $accountId, int $n = 50): Collection
  {
    return Transaction::where('account_id', $accountId)
      ->where('status', 'succeeded')
      ->orderBy('created_at', 'desc')
      ->limit($n)
      ->get()
      ->map(function ($t) {
        return [
          'when' => optional($t->created_at)->toDateTimeString(),
          'merchant' => $this->inferMerchant($t),
          'category' => $this->inferCategory($t),
          'amount' => (float) $t->amount,
          'type' => $t->type,
          'status' => $t->status,
          'description' => $t->description,
          'currency' => 'USD',
        ];
      });
  }

  public function monthlySpendingSummaryByAccount(int $accountId, int $months = 6): array
  {
    $from = now()->subMonths($months);
    $txns = Transaction::where('account_id', $accountId)
      ->where('status', 'succeeded')
      ->where('type', 'debit')
      ->where('created_at', '>=', $from)
      ->orderBy('created_at', 'desc')
      ->get();

    $bucket = [];
    foreach ($txns as $t) {
      $key = $t->created_at->format('Y-m');
      $bucket[$key] = ($bucket[$key] ?? 0) + (float) $t->amount;
    }
    krsort($bucket);
    return $bucket;
  }

  public function categorySpendingByAccount(int $accountId, int $months = 3): array
  {
    $from = now()->subMonths($months);
    $txns = Transaction::where('account_id', $accountId)
      ->where('status', 'succeeded')
      ->where('type', 'debit')
      ->where('created_at', '>=', $from)
      ->get();

    $totals = [];
    foreach ($txns as $t) {
      $cat = $this->inferCategory($t);
      $totals[$cat] = ($totals[$cat] ?? 0) + (float) $t->amount;
    }

    arsort($totals);
    $top = array_slice($totals, 0, 10, true);

    return collect($top)->map(fn($total, $cat) => ['category' => $cat, 'total' => (float) $total])->values()->toArray();
  }

  public function recurringMerchantsByAccount(int $accountId, int $months = 6, int $minTimes = 2): array
  {
    $from = now()->subMonths($months);
    $txns = Transaction::where('account_id', $accountId)
      ->where('status', 'succeeded')
      ->where('created_at', '>=', $from)
      ->get();

    $stats = [];
    foreach ($txns as $t) {
      $m = $this->inferMerchant($t);
      if (!isset($stats[$m])) $stats[$m] = ['times' => 0, 'total' => 0.0];
      $stats[$m]['times'] += 1;
      $stats[$m]['total'] += (float) $t->amount;
    }

    $stats = array_filter($stats, fn($s) => $s['times'] >= $minTimes);
    uasort($stats, fn($a, $b) => $b['times'] <=> $a['times']);

    $out = [];
    foreach ($stats as $merchant => $s) {
      $out[] = ['merchant' => $merchant, 'times' => $s['times'], 'total' => (float) $s['total']];
    }
    return $out;
  }

  public function largeTransactionsByAccount(int $accountId, int $months = 3, float $threshold = null): Collection
  {
    $from = now()->subMonths($months);

    $q = Transaction::where('account_id', $accountId)
      ->where('status', 'succeeded')
      ->where('type', 'debit')
      ->where('created_at', '>=', $from);

    if ($threshold !== null) {
      $q->where('amount', '>=', $threshold);
    }

    return $q->orderByDesc('amount')->limit(10)->get()->map(function ($t) {
      return [
        'amount' => (float) $t->amount,
        'merchant' => $this->inferMerchant($t),
        'when' => optional($t->created_at)->toDateTimeString(),
        'description' => $t->description,
      ];
    });
  }

  protected function inferMerchant($txn): string
  {
    if (!empty($txn->employee_name)) return trim($txn->employee_name);
    if (!empty($txn->account_related_id)) return 'Account#' . $txn->account_related_id;
    return $this->extractKeywordFromDescription($txn->description) ?? 'Unknown';
  }

  protected function inferCategory($txn): string
  {
    $desc = mb_strtolower($txn->description ?? '');
    $map = [
      'food' => ['restaurant', 'cafe', 'coffee', 'meal', 'مطعم', 'كافيه', 'أكل', 'وجبة'],
      'bills' => ['electric', 'water', 'internet', 'phone', 'gas', 'فاتورة', 'كهرباء', 'مياه', 'إنترنت', 'هاتف'],
      'entertainment' => ['cinema', 'movie', 'game', 'netflix', 'spotify', 'سينما', 'أفلام', 'ألعاب'],
      'transport' => ['uber', 'taxi', 'bus', 'fuel', 'gasoline', 'تاكسي', 'باص', 'وقود'],
      'subscriptions' => ['subscription', 'monthly', 'اشتراك', 'شهري'],
      'shopping' => ['store', 'market', 'mall', 'shop', 'سوق', 'متجر', 'مول', 'تسوق'],
    ];
    foreach ($map as $cat => $keywords) {
      foreach ($keywords as $kw) {
        if (mb_strpos($desc, $kw) !== false) return $cat;
      }
    }
    return $txn->type === 'credit' ? 'income' : 'other';
  }

  protected function extractKeywordFromDescription(?string $desc): ?string
  {
    if (!$desc) return null;
    $parts = preg_split('/\s+/u', trim($desc));
    return $parts ? ucfirst($parts[0]) : null;
  }
  public function allTransactions()
  {
    $user = Auth::user();

    if (in_array($user->role_id, [1, 2, 4, 5])) {
      return Transaction::all();
    }

    if ($user->role_id == 6) {
      return Transaction::query()
        ->where('user_id', $user->id)
        ->get();
    }

    return collect([]);
  }
}
