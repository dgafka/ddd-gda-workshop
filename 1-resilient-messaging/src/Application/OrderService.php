<?php

declare(strict_types=1);

namespace App\Application;

use App\Domain\Order;
use App\Domain\OrderRepository;
use App\Domain\ShippingService;
use Ecotone\Modelling\Attribute\CommandHandler;

final class OrderService
{
    #[CommandHandler]
    public function placeOrder(PlaceOrder $placeOrder, OrderRepository $orderRepository, ShippingService $shippingService): void
    {
        $order = Order::create($placeOrder->orderId, $placeOrder->productName);
        $orderRepository->save($order);

        $shippingService->ship($order);
    }
}