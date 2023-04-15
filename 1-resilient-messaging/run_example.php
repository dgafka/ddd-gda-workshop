<?php

use Ecotone\Lite\EcotoneLiteApplication;
use Enqueue\AmqpExt\AmqpConnectionFactory;
use Enqueue\Dbal\DbalConnectionFactory;

require __DIR__ . "/vendor/autoload.php";

$ecotoneLite = EcotoneLiteApplication::bootstrap([DbalConnectionFactory::class => new DbalConnectionFactory(getenv('DATABASE_DSN') ? getenv('DATABASE_DSN') : 'pgsql://ecotone:secret@localhost:5432/ecotone'), AmqpConnectionFactory::class => new AmqpConnectionFactory(['dsn' => getenv('RABBIT_DSN') ? getenv('RABBIT_DSN') : 'amqp://guest:guest@localhost:5672/%2f'])], pathToRootCatalog: __DIR__);
$commandBus = $ecotoneLite->getCommandBus();

