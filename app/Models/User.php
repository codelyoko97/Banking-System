<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
  /** @use HasFactory<\Database\Factories\UserFactory> */
  use HasFactory, Notifiable, HasApiTokens;

  /**
   * The attributes that are mass assignable.
   *
   * @var list<string>
   */
  protected $fillable = [
    'name',
    'email',
    'password',
    'phone',
    'is_verified',
    'role_id',
    'otp_code',
    'otp_expires_at',
    'failed_attempts',
    'locked_until'
  ];

  /**
   * The attributes that should be hidden for serialization.
   *
   * @var list<string>
   */
  protected $hidden = [
    'password',
    'remember_token',
    'otp_code'
  ];

  protected $casts = [
    'is_verified' => 'boolean',
    'otp_expires_at' => 'datetime',
    'locked_until' => 'datetime',
  ];

  public function role()
  {
    return $this->belongsTo(Role::class);
  }
  public function logs()
  {
    return $this->hasMany(Log::class);
  }
  public function notifications()
  {
    return $this->hasMany(Notification::class);
  }
  public function supportedTickets()
  {
    return $this->hasMany(SupportedTicket::class);
  }


  public function accounts()
  {
    return $this->hasMany(Account::class, 'customer_id');
  }


public function roleName(): ?string
{
    if ($this->relationLoaded('role') && $this->role) {
        return $this->role->name;
    }

    return $this->role ? $this->role->name : null;
}


public function hasRole(string|array $roles): bool
{
    $name = $this->roleName();
    if (!$name) return false;
    if (is_array($roles)) {
        return in_array($name, $roles, true);
    }
    return $name === $roles;
}

public function isAdmin(): bool { return $this->hasRole('Admin'); }
public function isManager(): bool { return $this->hasRole('Manager'); }
public function isCustomer(): bool { return $this->hasRole('Customer'); }
public function isTeller(): bool { return $this->hasRole('Teller'); }
public function isSupportAgent(): bool { return $this->hasRole('Support Agent'); }
public function isAuditor(): bool { return $this->hasRole('Auditor'); }










  /**
   * Get the attributes that should be cast.
   *
   * @return array<string, string>
   */
  // protected function casts(): array
  // {
  //     return [
  //         'email_verified_at' => 'datetime',
  //         'password' => 'hashed',
  //     ];
  // }
}
