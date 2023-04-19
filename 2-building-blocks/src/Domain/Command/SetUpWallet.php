<?php

declare(strict_types=1);

namespace App\Domain\Command;

final readonly class SetUpWallet
{
    public function __construct(
        public string $walletId,
    ) {}
}