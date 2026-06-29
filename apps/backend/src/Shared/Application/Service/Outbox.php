<?php

declare(strict_types=1);

namespace App\Shared\Application\Service;

use App\Shared\Domain\Event\DomainEvent;

interface Outbox
{
    public function record(DomainEvent ...$events): void;

    public function notify(): void;
}
