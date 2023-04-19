<?php

declare(strict_types=1);

namespace Tests\App;

use App\Domain\Command\DepositMoney;
use App\Domain\Command\SetUpWallet;
use App\Domain\Command\WithdrawMoney;
use App\Domain\Event\MoneyWasDeposited;
use App\Domain\Event\MoneyWasWithdrawn;
use App\Domain\Event\WalletWasSetUp;
use App\Domain\Wallet;
use App\ReadModel\WalletBalance;
use App\ReadModel\WalletBalanceGateway;
use Ecotone\Lite\EcotoneLite;
use PHPUnit\Framework\TestCase;

/**
 * Nie trzeba nic zmieniać w tych testach ;)
 */
final class WalletTest extends TestCase
{
    public function test_wallet_can_be_set_up(): void
    {
        $walletId = "123";

        TestCase::assertEquals(
            [new WalletWasSetUp($walletId)],
            EcotoneLite::bootstrapFlowTesting([Wallet::class])
                ->sendCommand(new SetUpWallet($walletId))
                ->getRecordedEvents()
        );
    }

    public function test_deposited_money_can_be_withdrawn(): void
    {
        $walletId = "123";

        TestCase::assertEquals(
            [new WalletWasSetUp($walletId), new MoneyWasDeposited($walletId, 100), new MoneyWasWithdrawn($walletId, 100)],
            EcotoneLite::bootstrapFlowTesting([Wallet::class])
                ->sendCommand(new SetUpWallet($walletId))
                ->sendCommand(new DepositMoney($walletId, 100))
                ->sendCommand(new WithdrawMoney($walletId, 100))
                ->getRecordedEvents()
        );
    }

    public function test_can_not_withdrawn_more_than_deposited(): void
    {
        $walletId = "123";

        $this->expectException(\RuntimeException::class);

        EcotoneLite::bootstrapFlowTesting([Wallet::class])
            ->sendCommand(new SetUpWallet($walletId))
            ->sendCommand(new DepositMoney($walletId, 100))
            ->sendCommand(new WithdrawMoney($walletId, 200));
    }

    public function test_increasing_current_balance_when_money_was_deposited(): void
    {
        $walletId = "123";

        TestCase::assertEquals(
            [$walletId => 100],
            EcotoneLite::bootstrapFlowTestingWithEventStore([Wallet::class, WalletBalance::class, WalletBalanceGateway::class], [new WalletBalance()])
                ->sendCommand(new SetUpWallet($walletId))
                ->sendCommand(new DepositMoney($walletId, 100))
                ->getGateway(WalletBalanceGateway::class)
                ->getBalance()
        );
    }

    public function test_decreasing_current_balance_when_money_was_withdrawn(): void
    {
        $walletId = '123';

        TestCase::assertEquals(
            [$walletId => 60],
            EcotoneLite::bootstrapFlowTestingWithEventStore([Wallet::class, WalletBalance::class, WalletBalanceGateway::class], [new WalletBalance()])
                ->sendCommand(new SetUpWallet($walletId))
                ->sendCommand(new DepositMoney($walletId, 100))
                ->sendCommand(new WithdrawMoney($walletId, 40))
                ->getGateway(WalletBalanceGateway::class)
                ->getBalance()
        );
    }
}