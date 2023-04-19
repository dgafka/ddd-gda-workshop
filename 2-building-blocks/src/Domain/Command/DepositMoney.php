<?php

declare(strict_types=1);

namespace App\Domain\Command;

final readonly class DepositMoney
{
    public function __construct(
        public string $walletId,
        public int $amount,
    ) {}
}