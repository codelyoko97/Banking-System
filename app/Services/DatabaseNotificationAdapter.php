<?php
namespace App\Services;

use App\Models\Notification;
use App\Models\User;

class DatabaseNotificationAdapter implements NotificationAdapterInterface
{
    public function sendToUser(int $userId, string $title, string $message): void
    {
        Notification::create([
            'user_id' => $userId,
            'content' => "{$title}: {$message}",
            'type' => 'ticket',
        ]);
    }

    public function sendToStaff(string $title, ?int $excludeUserId = null): void
    {
        $staff = User::where('role_id', '5')->get();
        foreach ($staff as $s) {
            if ($excludeUserId && $s->id == $excludeUserId) continue;
            Notification::create([
                'user_id' => $s->id,
                'content' => $title,
                'type' => 'ticket',
            ]);
        }
    }
}
