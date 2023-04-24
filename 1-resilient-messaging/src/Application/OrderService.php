<?php

declare(strict_types=1);

namespace App\Application;

use App\Domain\Order;
use App\Domain\OrderRepository;
use App\Domain\ShippingService;
use App\Event\OrderWasPlaced;
use Ecotone\Messaging\Attribute\Asynchronous;
use Ecotone\Modelling\Attribute\CommandHandler;
use Ecotone\Modelling\Attribute\EventHandler;
use Ecotone\Modelling\EventBus;
use App\Application\PlaceOrder;
final class OrderService
{
    #[Asynchronous("orders")]
    #[CommandHandler(endpointId: "order.place")]
    public function placeOrder(PlaceOrder $placeOrder, OrderRepository $orderRepository, EventBus $ebus): void
    {
        $order = Order::create($placeOrder->orderId, $placeOrder->productName);
        $orderRepository->save($order);
        $ebus->publish(new OrderWasPlaced($order->orderId),["orders"]);
    }

    #[Asynchronous("orders")]
    #[EventHandler(endpointId: "order_was_placed")] 
    public function handleOrder(OrderWasPlaced $order, ShippingService $shippingService): void {
        print("Lalalala, I am handling event");
        $shippingService->ship(new Order($order->orderId,"where do I get product name?"));
    }
}