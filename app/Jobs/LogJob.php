<?php

namespace App\Jobs;

use App\Models\Log;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class LogJob implements ShouldQueue
{
  use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

  public $tries = 3;
  public $timeout = 30;

  protected int $user_id;
  protected string $action;
  protected string $description;

  public function __construct(int $user_id, string $action, string $description)
  {
    $this->user_id = $user_id;
    $this->action = $action;
    $this->description = $description;
  }
  public function handle(): void
  {
    Log::create([
      'user_id' => $this->user_id,
      'action' => $this->action,
      'description' => $this->description,
    ]);
  }
}
