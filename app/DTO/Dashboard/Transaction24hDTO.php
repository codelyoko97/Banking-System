<?php

namespace App\DTO\Dashboard;

class Transaction24hDTO
{
  public static function build(array $row): array
  {
    return [
      'total' => (int)$row['total'],
      'success' => (int)$row['success'],
      'failed' => (int)$row['failed'],
      'pending' => (int)$row['pending'],
    ];
  }
}
