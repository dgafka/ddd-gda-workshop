<?php

declare(strict_types=1);

namespace App\Infrastructure;

use App\Domain\Order;
use App\Domain\ShippingService;

final class NetworkFailingShippingService implements ShippingService
{
    /**
     * Ta klasa imituje błąd sieciowy, który może wystąpić podczas wysyłki zamówienia.
     * Błąd wystąpi 3 razy, a potem zamówienie zostanie wysłane.
     */
    public function ship(Order $order): void
    {
        static $counter = 0;
        $counter++;

        if ($counter <= 3) {
            echo "Network error: $counter";
            throw new \RuntimeException("Network error");
        }
    }
}