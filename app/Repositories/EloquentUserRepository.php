<?php

namespace App\Repositories;

use App\Models\User;

class EloquentUserRepository implements UserRepositoryInterface {
    public function create(array $data): User {
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
}
