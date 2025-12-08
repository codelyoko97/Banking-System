<?php

namespace App\Services;

use App\DTO\Health\SystemHealthDTO;
use App\Repositories\SystemHealthRepository;
use Illuminate\Support\Facades\Cache;

class SystemHealthService
{
    public function __construct(private SystemHealthRepository $repo) {}

    public function get(): array
    {
        return Cache::remember('system.health', 10, function () {
            return (new SystemHealthDTO(
                db: $this->repo->dbStatus(),
                cache: $this->repo->cacheStatus(),
                queue: $this->repo->queueStatus(),
                requests: $this->repo->requestStats(),
                system: $this->repo->systemUsage()
            ))->toArray();
        });
    }
}
