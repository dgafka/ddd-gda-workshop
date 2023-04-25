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

$serviceName = 'ddd_gda_service';
$shippingService = new NetworkFailingShippingService();
$ecotoneLite = EcotoneLiteApplication::bootstrap([ShippingService::class => $shippingService, NetworkFailingShippingService::class => $shippingService, DbalConnectionFactory::class => new DbalConnectionFactory(getenv('DATABASE_DSN') ? getenv('DATABASE_DSN') : 'pgsql://ecotone:secret@localhost:4002/ecotone'), AmqpConnectionFactory::class => new AmqpConnectionFactory(['dsn' => getenv('RABBIT_DSN') ? getenv('RABBIT_DSN') : 'amqp://guest:guest@localhost:4003/%2f'])], serviceConfiguration: ServiceConfiguration::createWithDefaults()->withServiceName($serviceName)->withDefaultErrorChannel('errorChannel'), pathToRootCatalog: __DIR__);
cleanup($ecotoneLite);
$executionPollingMetadata = ExecutionPollingMetadata::createWithDefaults()->withExecutionTimeLimitInMilliseconds(1000)->withHandledMessageLimit(1);

echo "\n\n\nSkładamy zamówienia na Laptopa\n";
$ecotoneLite->getCommandBus()->send(new PlaceOrder(Uuid::uuid4()->toString(), "Laptop"));

echo "Ecotone stworzy dla Ciebie kolejkę w RabbitMQ (http://localhost:4001/#/queues) oraz serializuje/odserializuje wiadomości. Błąd przetwarzania wiadomości wystąpi cztery razy.\n\n";
echo "Oczekuje na asynchroniczną wiadomość.\n";
$ecotoneLite->run("orders", $executionPollingMetadata);
echo "Pierwsze przetworzenie wiadomości zakończone, jesteśmy coraz blizej!\n\n";

echo "Ponawiam wiadomość. Drugie przetworzenie wiadomości.\n";
sleep(2);
$ecotoneLite->run('orders', $executionPollingMetadata);
echo "\nPonawiam wiadomość. Trzecie przetworzenie wiadomości.\n";
sleep(2);
$ecotoneLite->run('orders', $executionPollingMetadata);
echo "\nPonawiam wiadomość. Czwarte przetworzenie wiadomości.\n";
sleep(2);
$ecotoneLite->run('orders', $executionPollingMetadata);

echo "\n\nTwoja wiadomość wyczerpała liczbę ponowień i została zapisana w Dead Letter.\n";
echo "Ponów wiadomość bezpośrednio z Ecotone Pulse: http://localhost:4000, aby zakończyć zadanie :)\n";

/** Consumer dla wiadomości rozproszonych (komunikacja z Ecotone Pulse) */
$ecotoneLite->run($serviceName, ExecutionPollingMetadata::createWithDefaults()->withExecutionTimeLimitInMilliseconds(60000)->withHandledMessageLimit(1));
$ecotoneLite->run('orders', $executionPollingMetadata);

Assert::assertTrue($ecotoneLite->getQueryBus()->sendWithRouting("isShippingSuccessful"));
echo "Udało się przetworzyć wiadomość i dostarczyć laptopa do klienta. Gratulacje, zadanie ukończone!\n\n";

function cleanup(\Ecotone\Messaging\Config\ConfiguredMessagingSystem $ecotoneLite): void
{
    /** @var AmqpConnectionFactory $amqpConnectionFactory */
    $amqpConnectionFactory = $ecotoneLite->getServiceFromContainer(AmqpConnectionFactory::class);
    $amqpConnectionFactory->createContext()->deleteQueue(new \Interop\Amqp\Impl\AmqpQueue('orders'));
    $amqpConnectionFactory->createContext()->deleteQueue(new \Interop\Amqp\Impl\AmqpQueue('distributed_gda_service'));
    $ecotoneLite->getGatewayByName(DeadLetterGateway::class)->deleteAll();
}