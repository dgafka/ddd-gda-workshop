<?php

use App\Application\PlaceOrder;
use App\Domain\ShippingService;
use App\Infrastructure\NetworkFailingShippingService;
use Ecotone\Lite\EcotoneLiteApplication;
use Ecotone\Messaging\Endpoint\ExecutionPollingMetadata;
use Enqueue\AmqpExt\AmqpConnectionFactory;
use Enqueue\Dbal\DbalConnectionFactory;

require __DIR__ . "/vendor/autoload.php";

$networkFailingShippingService = new NetworkFailingShippingService();
$ecotoneLite = EcotoneLiteApplication::bootstrap([ShippingService::class => $networkFailingShippingService], [DbalConnectionFactory::class => new DbalConnectionFactory(getenv('DATABASE_DSN') ? getenv('DATABASE_DSN') : 'pgsql://ecotone:secret@localhost:5432/ecotone'), AmqpConnectionFactory::class => new AmqpConnectionFactory(['dsn' => getenv('RABBIT_DSN') ? getenv('RABBIT_DSN') : 'amqp://guest:guest@localhost:5672/%2f'])], pathToRootCatalog: __DIR__);

$commandBus = $ecotoneLite->getCommandBus();
$queryBus = $ecotoneLite->getQueryBus();

echo "Składamy zamówienia na Laptopa\n";
$commandBus->send(new PlaceOrder("1", "Laptop"));

echo "Oczekuje na asynchroniczną wiadomość\n";
$ecotoneLite->run("orders", ExecutionPollingMetadata::createWithDefaults()->withTestingSetup(maxExecutionTimeInMilliseconds: 1000));
echo "Ponawiam wiadomość pierwszy raz\n";
$ecotoneLite->run('orders', ExecutionPollingMetadata::createWithDefaults()->withTestingSetup(maxExecutionTimeInMilliseconds: 1000));
echo "Ponawiam wiadomość drugi raz\n";
$ecotoneLite->run('orders', ExecutionPollingMetadata::createWithDefaults()->withTestingSetup(maxExecutionTimeInMilliseconds: 1000));
echo "Ponawiam wiadomość trzeci raz\n";
$ecotoneLite->run('orders', ExecutionPollingMetadata::createWithDefaults()->withTestingSetup(maxExecutionTimeInMilliseconds: 1000));

echo "Twoja wiadomość wyczerpała liczbę ponowień i została zapisana w Dead Letter i można zobaczyć ją w Ecotone Pulse: http://localhost:3000\n";
echo "Ponów wiadomość bezpośrednio z Ecotone Pulse: http://localhost:3000, aby zakończyć zadanie :)\n";

$ecotoneLite->run('orders', ExecutionPollingMetadata::createWithDefaults()->withTestingSetup(maxExecutionTimeInMilliseconds: 60000));

echo "Udało się dostarczyć zamówienie do klienta. Gratulacje, ukończyłeś zadanie!\n";