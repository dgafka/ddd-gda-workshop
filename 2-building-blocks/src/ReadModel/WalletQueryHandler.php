<?php

declare(strict_types=1);

namespace App\ReadModel;

use Ecotone\Modelling\Attribute\QueryHandler;

final class WalletQueryHandler
{
    #[QueryHandler("getBalance")]
    public function getBalance(string $walletId, WalletBalanceGateway $walletBalanceGateway): int
    {
        $balances = $walletBalanceGateway->getBalance();
        if (!isset($balances[$walletId])) {
            throw new \InvalidArgumentException("Wallet with id {$walletId} does not exist");
        }

        return $balances[$walletId];
    }
}