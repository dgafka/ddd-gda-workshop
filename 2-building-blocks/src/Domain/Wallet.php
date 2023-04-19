<?php

declare(strict_types=1);

namespace App\Domain;

use App\Domain\Command\DepositMoney;
use App\Domain\Command\SetUpWallet;
use App\Domain\Command\WithdrawMoney;
use App\Domain\Event\MoneyWasDeposited;
use App\Domain\Event\MoneyWasWithdrawn;
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

    private int $balance = 0;

    #[CommandHandler]
    public static function setUp(SetUpWallet $command): array
    {
        return [new WalletWasSetUp($command->walletId)];
    }

    #[CommandHandler]
    public function deposit(DepositMoney $command): array
    {
        return [new MoneyWasDeposited($command->walletId, $command->amount)];
    }

    #[CommandHandler]
    public function withdraw(WithdrawMoney $command): array
    {
        return [];
    }

    #[EventSourcingHandler]
    public function applyWalletWasSetUp(WalletWasSetUp $event): void
    {
        $this->walletId = $event->walletId;
    }

    #[EventSourcingHandler]
    public function applyMoneyWasDeposited(MoneyWasDeposited $event): void
    {
        $this->balance += $event->amount;
    }
}