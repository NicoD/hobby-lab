<?php

declare(strict_types=1);

namespace App\Shared\Application\Service;

use App\Shared\Domain\Event\DomainEvent;

interface DomainEventDispatcher
{
    public function dispatch(DomainEvent ...$events): void;
}
