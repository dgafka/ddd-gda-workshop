<?php

declare(strict_types=1);

namespace App\ReadModel;

use App\Domain\Event\MoneyWasDeposited;
use App\Domain\Event\MoneyWasWithdrawn;
use App\Domain\Event\WalletWasSetUp;
use App\Domain\Wallet;
use Ecotone\EventSourcing\Attribute\Projection;
use Ecotone\EventSourcing\Attribute\ProjectionState;
use Ecotone\Modelling\Attribute\EventHandler;

#[Projection("walletBalance", Wallet::class)]
final class WalletBalance
{
    #[EventHandler]
    public function whenWalletWasSetUp(WalletWasSetUp $event, #[ProjectionState] array $state): array
    {
        $state[$event->walletId] = 0;

        return $state;
    }
}