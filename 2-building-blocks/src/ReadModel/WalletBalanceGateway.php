<?php

declare(strict_types=1);

namespace App\ReadModel;

use Ecotone\EventSourcing\Attribute\ProjectionStateGateway;

interface WalletBalanceGateway
{
    #[ProjectionStateGateway("walletBalance")]
    public function getBalance(): array;
}