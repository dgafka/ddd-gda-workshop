<?php

declare(strict_types=1);

namespace App\Infrastructure;

use Ecotone\Amqp\Configuration\AmqpMessageConsumerConfiguration;
use Ecotone\Amqp\Distribution\AmqpDistributedBusConfiguration;
use Ecotone\Dbal\Configuration\DbalConfiguration;
use Ecotone\Messaging\Attribute\ServiceContext;
use Ecotone\Messaging\Handler\Recoverability\ErrorHandlerConfiguration;
use Ecotone\Messaging\Handler\Recoverability\RetryTemplateBuilder;

/**
 * To zadanie można rozwiązać bez zmiany tej klasy :)
 */
final class EcotoneConfiguration
{
    #[ServiceContext]
    public function retryConfiguration(): ErrorHandlerConfiguration
    {
        /**
         * Konfiguracja dla asynchrocznicznego przetwarzania wiadomości.
         * Która będzie próbować przetworzyć wiadomość 3 razy z interwałem 100ms.
         * Jeśli nie uda się przetworzyć wiadomości, to zostanie zapisane w dead letter, znajdującym się w Bazie danych.
         */
        return ErrorHandlerConfiguration::createWithDeadLetterChannel(
            'errorChannel',
            RetryTemplateBuilder::fixedBackOff(100)
                ->maxRetryAttempts(3),
            'dbal_dead_letter'
        );
    }

    #[ServiceContext]
    public function aggregateRepository(): DbalConfiguration
    {
        /**
         *  Korzystamy z gotowego repozytorium, który zajme się serializacja Order'u i zapisaniem go w Bazie danych.
         */
        return DbalConfiguration::createWithDefaults()
                ->withDocumentStore(enableDocumentStoreAggregateRepository: true);
    }

    #[ServiceContext]
    public function distributedConsumer(): AmqpDistributedBusConfiguration
    {
        /**
         * Potrzebne dla komunikacji z Ecotone Pulse
         */
        return AmqpDistributedBusConfiguration::createConsumer();
    }
}