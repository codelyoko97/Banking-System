<?php
namespace App\Banking\Accounts;

interface AccountComponent {
    public function getId(): int;
    public function getBalance(): float;
    public function deposit(float $amount, array $meta = []): bool;
    public function withdraw(float $amount, array $meta = []): bool;
    public function getChildren(): array;
}
