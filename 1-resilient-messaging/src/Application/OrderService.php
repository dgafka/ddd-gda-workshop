<?php

declare(strict_types=1);

namespace App\Application;

use App\Domain\OrderRepository;
use Ecotone\Modelling\Attribute\CommandHandler;

final class OrderService
{
    #[CommandHandler]
    public function placeOrder(PlaceOrder $placeOrder, OrderRepository $orderRepository): void
    {

    }
}