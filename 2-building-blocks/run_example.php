<?php

use App\Application\PlaceOrder;
use App\Domain\Command\DepositMoney;
use App\Domain\Command\SetUpWallet;
use App\Domain\Command\WithdrawMoney;
use App\Domain\ShippingService;
use App\Infrastructure\NetworkFailingShippingService;
use App\ReadModel\WalletBalanceGateway;
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

$commandBus = $ecotoneLite->getCommandBus();
$queryBus = $ecotoneLite->getQueryBus();

echo "\n\nTo jest produkcje wywołanie Ecotone'a. Ecotone zajmie się serializacją i deserializacją eventów i przygotuje dla Ciebie Event Store'a oraz stan projekcji w Postgresie\n";
echo "Możesz sprawdzić bazę danych, aby zobaczyć jak wyglada Event Stream\n";
sleep(1);

$walletId = Uuid::uuid4()->toString();
echo "Tworzymy portfel o id: $walletId\n";
$commandBus->send(new SetUpWallet($walletId));
echo "Wpłacamy 100.\n";
$commandBus->send(new DepositMoney($walletId, 100));
echo "Wypłacamy 60.\n";
$commandBus->send(new WithdrawMoney($walletId, 60));

echo "Sprawdzamy stan portfela.\n";
Assert::assertEquals(
    40,
    $queryBus->sendWithRouting('getBalance', $walletId)
);

echo "Stan portfela się zgadza. Gratulacje ukończyłeś warsztaty!\n";