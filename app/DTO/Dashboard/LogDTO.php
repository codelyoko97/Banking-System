<?php

namespace App\DTO\Dashboard;

class LogDTO
{
    public static function transform($log): array
    {
        return [
            'id'         => $log->id,
            'user'       => $log->user?->name,
            'email'      => $log->user?->email,
            'action'     => $log->action,
            'description'=> $log->description,
            'date'       => $log->created_at->toDateTimeString(),
        ];
    }
}
