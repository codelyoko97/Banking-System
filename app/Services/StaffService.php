<?php

namespace App\Services;

use App\DTO\Dashboard\StaffDTO as DashboardStaffDTO;
use App\DTOs\StaffDTO;
use App\Models\Account;
use App\Models\User;
use App\Repositories\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;

class StaffService
{
  public function __construct(
    private UserRepositoryInterface $repo
  ) {}

  public function createStaff(DashboardStaffDTO $dto)
  {
    return $this->repo->createStaff([
      'name' => $dto->name,
      'email' => $dto->email,
      'password' => Hash::make($dto->password),
      'phone' => $dto->phone,
      'role_id' => $dto->role_id,
      'is_verified' => true,
    ]);
  }

  public function listStaff()
  {
    return $this->repo->allStaff();
  }

  public function updateRole(int $userId, int $roleId)
  {
    $user = User::findOrFail($userId);
    return $this->repo->updateRole($user, $roleId);
  }

  public function getAllEmployees()
  {
    return User::where('role_id', 4)->get();
  }

  public function deleteStaff(int $userId)
  {
    $user = User::findOrFail($userId);
    return $this->repo->delete($user);
  }

  public function getAccountUser(string $accountNumber)
  {
    $account = Account::where('number', $accountNumber)->firstOrFail();
    return User::where('id', $account->customer_id)->first();
  }
}
