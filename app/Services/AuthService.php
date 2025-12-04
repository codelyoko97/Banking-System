<?php

namespace App\Services;

use App\Jobs\SendOtpMailJob;
use App\Models\User;
use App\Repositories\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthService {
    protected $users;

  public function __construct(UserRepositoryInterface $users)
  {
    $this->users = $users;
  }

  public function registerCitizen(array $data): User
  {
    $data['password'] = Hash::make($data['password']);
    $data['role_id'] = 4;
    $user = $this->users->create($data);
    $this->generateAndSendOtp($user);
    $this->log($user->id, 'register', ['method' => 'citizen']);
    return $user;
  }

  public function generateAndSendOtp(User $user)
  {
    $otp = (string) rand(100000, 999999);

    $this->users->update($user, [
      'otp_code' => $otp,
      'otp_expires_at' => now()->addMinutes(10),
      'is_verified' => false,
    ]);

    SendOtpMailJob::dispatch($user, $otp);

    // مثال لتأخير التنفيذ 5 ثواني:
    // SendOtpMailJob::dispatch($user, $otp)->delay(now()->addSeconds(5));

    $this->log($user->id, 'otp_sent', ['otp_length' => 6]);
  }



  public function verifyOtp(User $user, string $otp): bool
  {
    if (!$user->otp_code || $user->otp_code !== $otp) return false;
    if ($user->otp_expires_at && now()->greaterThan($user->otp_expires_at)) return false;

    $this->users->update($user, [
      'is_verified' => true,
      'otp_code' => null,
      'otp_expires_at' => null,
    ]);
    $this->log($user->id, 'otp_verified', null);
    return true;
  }

  public function attemptLogin(string $identifier, string $password): array
  {
    $user = $this->users->findByEmailOrPhone($identifier);
    $ip = request()->ip();

    if (!$user) {
      $this->log(null, 'login_failed', ['identifier' => $identifier, 'ip' => $ip]);
      return ['success' => false, 'message' => 'Invalid credentials'];
    }

    if ($user->locked_until && now()->lessThan($user->locked_until)) {
      return ['success' => false, 'message' => 'Account locked until ' . $user->locked_until];
    }

    if (!Hash::check($password, $user->password)) {
      $user->failed_attempts++;
      $update = ['failed_attempts' => $user->failed_attempts];
      if ($user->failed_attempts >= 5) {
        $update['locked_until'] = now()->addMinutes(15);
        $update['failed_attempts'] = 0;
        $this->log($user->id, 'account_locked', null);
      }
      $this->users->update($user, $update);
      $this->log($user->id, 'login_failed', ['ip' => $ip]);
      return ['success' => false, 'message' => 'Invalid credentials'];
    }

    // success
    $this->users->update($user, ['failed_attempts' => 0, 'locked_until' => null]);
    return ['success' => true, 'user' => $user];
  }

  protected function log($userId, string $action, ?array $meta)
  {
    Log::info('AuthService action', [
      'user_id' => $userId,
      'action' => $action,
      'meta' => $meta,
      'ip' => request()->ip(),
    ]);
  }
}
