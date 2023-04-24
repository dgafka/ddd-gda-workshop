<?php

declare(strict_types=1);

namespace App\Event;

final class OrderWasPlaced
{
    public function __construct(
        public string $orderId
    ) {}
}