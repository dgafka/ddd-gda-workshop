<?php

use App\Application\PlaceOrder;
use App\Domain\ShippingService;
use App\Infrastructure\NetworkFailingShippingService;
use Ecotone\Lite\EcotoneLiteApplication;
use Ecotone\Messaging\Config\ServiceConfiguration;
use Ecotone\Messaging\Endpoint\ExecutionPollingMetadata;
use Enqueue\AmqpExt\AmqpConnectionFactory;
use Enqueue\Dbal\DbalConnectionFactory;
use PHPUnit\Framework\Assert;
use Ramsey\Uuid\Uuid;

require __DIR__ . "/vendor/autoload.php";

$ecotoneLite = EcotoneLiteApplication::bootstrap([ShippingService::class => new NetworkFailingShippingService(), DbalConnectionFactory::class => new DbalConnectionFactory(getenv('DATABASE_DSN') ? getenv('DATABASE_DSN') : 'pgsql://ecotone:secret@localhost:5432/ecotone'), AmqpConnectionFactory::class => new AmqpConnectionFactory(['dsn' => getenv('RABBIT_DSN') ? getenv('RABBIT_DSN') : 'amqp://guest:guest@localhost:5672/%2f'])], serviceConfiguration: ServiceConfiguration::createWithDefaults()->withServiceName('ddd_gda_service')->withDefaultErrorChannel('errorChannel'), pathToRootCatalog: __DIR__);
/** @var AmqpConnectionFactory $amqpConnectionFactory */
$amqpConnectionFactory = $ecotoneLite->getServiceFromContainer(AmqpConnectionFactory::class);
$amqpConnectionFactory->createContext()->deleteQueue(new \Interop\Amqp\Impl\AmqpQueue('orders'));
$executionPollingMetadata = ExecutionPollingMetadata::createWithDefaults()->withExecutionTimeLimitInMilliseconds(2000)->withHandledMessageLimit(1);

$commandBus = $ecotoneLite->getCommandBus();
$queryBus = $ecotoneLite->getQueryBus();

echo "Składamy zamówienia na Laptopa\n";
$commandBus->send(new PlaceOrder(Uuid::uuid4()->toString(), "Laptop"));

echo "Oczekuje na asynchroniczną wiadomość. Błąd przetwarzania wiadomości wystąpi cztery razy.\n";
$ecotoneLite->run("orders", $executionPollingMetadata);
echo "Ecotone stworzył dla Ciebie kolejkę w RabbitMQ (http://localhost:15672/#/queues) oraz serializował/odserializował wiadomość. Pierwsze przetworzenie wiadomości zakończone, jesteśmy coraz blizej!\n";

echo "Ponawiam wiadomość. Drugie przetworzenie wiadomości.\n";
sleep(2);
$ecotoneLite->run('orders', $executionPollingMetadata);
echo "Ponawiam wiadomość. Trzecie przetworzenie wiadomości.\n";
sleep(2);
$ecotoneLite->run('orders', $executionPollingMetadata);
echo "Ponawiam wiadomość. Czwarte przetworzenie wiadomości.\n";
sleep(2);
$ecotoneLite->run('orders', $executionPollingMetadata);

echo "Twoja wiadomość wyczerpała liczbę ponowień i została zapisana w Dead Letter.\n";
echo "Ponów wiadomość bezpośrednio z Ecotone Pulse: http://localhost:3000, aby zakończyć zadanie :)\n";

$ecotoneLite->run('orders', ExecutionPollingMetadata::createWithDefaults()->withExecutionTimeLimitInMilliseconds(60000)->withHandledMessageLimit(1)->withStopOnError(true));

Assert::assertTrue($ecotoneLite->getQueryBus()->sendWithRouting("isShippingSuccessful"));
echo "Udało się dostarczyć zamówienie do klienta. Gratulacje, zadanie ukończone!\n";