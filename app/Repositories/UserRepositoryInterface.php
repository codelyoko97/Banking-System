<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Support\Collection;

interface UserRepositoryInterface
{
  public function create(array $data): User;
  public function findByEmailOrPhone(string $identifier): ?User;
  public function findById(int $id): ?User;
  public function update(User $user, array $data): bool;

  public function createStaff(array $data): User;
  public function allStaff(): Collection;
  public function updateRole(User $user, int $roleId): User;
  public function delete(User $user): bool;
}
