<?php

declare(strict_types=1);

namespace App\Domain;

use Ecotone\Modelling\Attribute\Aggregate;
use Ecotone\Modelling\Attribute\AggregateIdentifier;

#[Aggregate]
final class Order
{
    public function __construct(
        #[AggregateIdentifier] public readonly string $orderId,
        public readonly string $productId
    ) {}

    public static function create(string $userId, string $productId)
    {
        return new self($userId, $productId);
    }
}