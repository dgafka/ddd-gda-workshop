<?php

declare(strict_types=1);

namespace Tests\App;

use App\Domain\Command\SetUpWallet;
use App\Domain\Event\WalletWasSetUp;
use App\Domain\Wallet;
use Ecotone\Lite\EcotoneLite;
use PHPUnit\Framework\TestCase;

final class WalletTest extends TestCase
{
    public function test_wallet_can_be_set_up(): void
    {
        $walletId = "123";

        $this->assertEquals(
            [new WalletWasSetUp($walletId)],
            EcotoneLite::bootstrapFlowTesting([Wallet::class])
                ->sendCommand(new SetUpWallet($walletId))
                ->getRecordedEvents()
        );
    }
}