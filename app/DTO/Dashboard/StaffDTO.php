<?php

namespace App\DTO\Dashboard;

class StaffDTO
{
  public function __construct(
    public string $name,
    public string $email,
    public string $password,
    public string $phone,
    public int $role_id
  ) {}

  public static function fromRequest($request): self
  {
    return new self(
      name: $request->name,
      email: $request->email,
      password: $request->password,
      phone: $request->phone,
      role_id: $request->role_id
    );
  }
}
