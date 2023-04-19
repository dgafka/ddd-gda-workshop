<?php

declare(strict_types=1);

namespace App\Domain\Event;

final class MoneyWasDeposited
{
    public function __construct(
        public string $walletId,
        public int $amount,
    ) {}
}