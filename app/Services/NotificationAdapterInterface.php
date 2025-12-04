<?php
namespace App\Services;

interface NotificationAdapterInterface
{
    public function sendToUser(int $userId, string $title, string $message): void;
    public function sendToStaff(string $title, ?int $excludeUserId = null): void;
}
