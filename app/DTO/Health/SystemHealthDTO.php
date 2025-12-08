<?php

namespace App\DTO\Health;

class SystemHealthDTO
{
    public function __construct(
        public array $db,
        public array $cache,
        public array $queue,
        public array $requests,
        public array $system
    ) {}

    public function toArray(): array
    {
        return [
            'db' => $this->db,
            'cache' => $this->cache,
            'queue' => $this->queue,
            'requests' => $this->requests,
            'system' => $this->system
        ];
    }
}
