<?php

declare(strict_types=1);

namespace App\Shared\Domain\Event;

use Symfony\Component\Uid\Uuid;

class DomainEventId implements \Stringable
{
    protected string $id;

    public function __construct()
    {
        $this->id = Uuid::v7()->toRfc4122();
    }

    #[\Override]
    public function __toString(): string
    {
        return $this->id;
    }
}
