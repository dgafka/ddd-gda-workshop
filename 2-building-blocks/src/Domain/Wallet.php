<?php

declare(strict_types=1);

namespace App\Domain;

use App\Domain\Command\SetUpWallet;
use App\Domain\Event\WalletWasSetUp;
use Ecotone\Modelling\Attribute\AggregateIdentifier;
use Ecotone\Modelling\Attribute\CommandHandler;
use Ecotone\Modelling\Attribute\EventSourcingAggregate;
use Ecotone\Modelling\Attribute\EventSourcingHandler;
use Ecotone\Modelling\WithAggregateVersioning;

#[EventSourcingAggregate]
final class Wallet
{
    use WithAggregateVersioning;

    #[AggregateIdentifier]
    private string $walletId;

    #[CommandHandler]
    public static function setUp(SetUpWallet $setUpWallet): array
    {
        return [new WalletWasSetUp($setUpWallet->walletId)];
    }

    #[EventSourcingHandler]
    public function applyWalletWasSetUp(WalletWasSetUp $walletWasSetUp): void
    {
        $this->walletId = $walletWasSetUp->walletId;
    }
}