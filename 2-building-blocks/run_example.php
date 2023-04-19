<?php

use App\Application\PlaceOrder;
use App\Domain\ShippingService;
use App\Infrastructure\NetworkFailingShippingService;
use Ecotone\Dbal\Recoverability\DeadLetterGateway;
use Ecotone\Lite\EcotoneLiteApplication;
use Ecotone\Messaging\Config\ServiceConfiguration;
use Ecotone\Messaging\Endpoint\ExecutionPollingMetadata;
use Enqueue\AmqpExt\AmqpConnectionFactory;
use Enqueue\Dbal\DbalConnectionFactory;
use PHPUnit\Framework\Assert;
use Ramsey\Uuid\Uuid;

require __DIR__ . "/vendor/autoload.php";

$ecotoneLite = EcotoneLiteApplication::bootstrap([DbalConnectionFactory::class => new DbalConnectionFactory(getenv('DATABASE_DSN') ? getenv('DATABASE_DSN') : 'pgsql://ecotone:secret@localhost:5432/ecotone')], pathToRootCatalog: __DIR__);
cleanup($ecotoneLite);

$commandBus = $ecotoneLite->getCommandBus();
$queryBus = $ecotoneLite->getQueryBus();


function cleanup(\Ecotone\Messaging\Config\ConfiguredMessagingSystem $ecotoneLite): void
{

}