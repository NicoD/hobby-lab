<?php

declare(strict_types=1);

namespace Outbox;

use Outbox\DependencyInjection\OutboxExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class OutboxBundle extends Bundle
{
    #[\Override]
    public function getContainerExtension(): OutboxExtension
    {
        return new OutboxExtension();
    }
}
