<?php

declare(strict_types=1);

namespace App\Infrastructure;

use Ecotone\Messaging\Attribute\ServiceContext;

final class MessageChannelConfiguration
{
    #[ServiceContext]
    public function asynchronousMessageChannel()
    {
        /**
         *  Dodaj tutaj konfigurację dla asynchronicznego kanału wiadomości.
         */

        return [];
    }
}