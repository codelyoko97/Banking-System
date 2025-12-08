<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Support\Collection;

class EloquentUserRepository implements UserRepositoryInterface
{
  public function create(array $data): User
  {
    return User::create($data);
  }

  public function findByEmailOrPhone(string $identifier): ?User
  {
    return User::where('email', $identifier)->orWhere('phone', $identifier)->first();
  }

  public function findById(int $id): ?User
  {
    return User::find($id);
  }

  public function update(User $user, array $data): bool
  {
    return $user->update($data);
  }


  public function createStaff(array $data): User
  {
    $data['password'] = bcrypt('123123123'); 
    return User::create($data);
  }

  public function allStaff(): Collection
  {
    return User::with('role')->whereHas(
      'role',
      fn($q) =>
      $q->where('name', '!=', 'Customer')
    )->get();
  }

  public function updateRole(User $user, int $roleId): User
  {
    $user->update(['role_id' => $roleId]);
    return $user->fresh();
  }

  public function delete(User $user): bool
  {
    return $user->delete();
  }
}
