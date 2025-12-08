<?php

namespace App\Services;

use App\Banking\Transactions\Approval\ManagerApproval;
use App\Banking\Transactions\Approval\SystemApproval;
use App\Banking\Transactions\Approval\TellerApproval;
use App\DTO\ProcessTransactionDTO;
use App\Repositories\TransactionRepositoryInterface;

class TransactionService
{
  protected TransactionRepositoryInterface $transactionRepo;

  public function __construct(TransactionRepositoryInterface $transactionRepo)
  {
    $this->transactionRepo = $transactionRepo;
  }
  public function process(ProcessTransactionDTO $dto)
  {
    $system = new SystemApproval();
    $teller = new TellerApproval();
    $manager = new ManagerApproval();

    $system->setNext($teller, 4)->setNext($manager, 2);
    return $system->approve($dto);
  }

  public function approve($id)
  {
    return $this->transactionRepo->approve($id);
  }

  public function reject($id)
  {
    return $this->transactionRepo->reject($id);
  }

  public function addPlan(array $data)
  {
    return $this->transactionRepo->createSchedule($data);
  }

  public function showTransactions()
  {
    return $this->transactionRepo->showTransactions();
  }
}
