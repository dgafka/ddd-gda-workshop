<?php

use App\Application\PlaceOrder;
use App\Domain\ShippingService;
use App\Infrastructure\NetworkFailingShippingService;
use Ecotone\Lite\EcotoneLiteApplication;
use Ecotone\Messaging\Config\ServiceConfiguration;
use Ecotone\Messaging\Endpoint\ExecutionPollingMetadata;
use Enqueue\AmqpExt\AmqpConnectionFactory;
use Enqueue\Dbal\DbalConnectionFactory;

require __DIR__ . "/vendor/autoload.php";

$networkFailingShippingService = new NetworkFailingShippingService();
$ecotoneLite = EcotoneLiteApplication::bootstrap([ShippingService::class => $networkFailingShippingService, DbalConnectionFactory::class => new DbalConnectionFactory(getenv('DATABASE_DSN') ? getenv('DATABASE_DSN') : 'pgsql://ecotone:secret@localhost:5432/ecotone'), AmqpConnectionFactory::class => new AmqpConnectionFactory(['dsn' => getenv('RABBIT_DSN') ? getenv('RABBIT_DSN') : 'amqp://guest:guest@localhost:5672/%2f'])], serviceConfiguration: ServiceConfiguration::createWithDefaults()->withServiceName('ddd_gda_service'), pathToRootCatalog: __DIR__);

$commandBus = $ecotoneLite->getCommandBus();
$queryBus = $ecotoneLite->getQueryBus();

echo "Składamy zamówienia na Laptopa\n";
$commandBus->send(new PlaceOrder("1", "Laptop"));

echo "Oczekuje na asynchroniczną wiadomość. Błąd przetwarzania wiadomości wystąpi cztery razy.\n";
sleep(1);
$ecotoneLite->run("orders", ExecutionPollingMetadata::createWithDefaults()->withTestingSetup(maxExecutionTimeInMilliseconds: 1000));
echo "Ecotone stworzył dla Ciebie kolejkę w RabbitMQ (http://localhost:15672/#/queues) oraz serializował/odserializował wiadomość. Pierwsze przetworzenie wiadomości zakończone, jesteśmy coraz blizej!\n";
sleep(1);
echo "Ponawiam wiadomość. Drugie przetworzenie wiadomości.\n";
$ecotoneLite->run('orders', ExecutionPollingMetadata::createWithDefaults()->withTestingSetup(maxExecutionTimeInMilliseconds: 1000));
echo "Ponawiam wiadomość. Trzecie przetworzenie wiadomości.\n";
$ecotoneLite->run('orders', ExecutionPollingMetadata::createWithDefaults()->withTestingSetup(maxExecutionTimeInMilliseconds: 1000));
echo "Ponawiam wiadomość. Czwarte przetworzenie wiadomości.\n";
$ecotoneLite->run('orders', ExecutionPollingMetadata::createWithDefaults()->withTestingSetup(maxExecutionTimeInMilliseconds: 1000));

echo "Twoja wiadomość wyczerpała liczbę ponowień i została zapisana w Dead Letter.\n";
echo "Ponów wiadomość bezpośrednio z Ecotone Pulse: http://localhost:3000, aby zakończyć zadanie :)\n";

$ecotoneLite->run('orders', ExecutionPollingMetadata::createWithDefaults()->withTestingSetup(maxExecutionTimeInMilliseconds: 120000));

echo "Udało się dostarczyć zamówienie do klienta. Gratulacje, zadanie ukończone!\n";