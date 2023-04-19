<?php

declare(strict_types=1);

namespace App\Domain\Event;

final readonly class WalletWasSetUp
{
    public function __construct(
        public string $walletId,
    ) {}
}