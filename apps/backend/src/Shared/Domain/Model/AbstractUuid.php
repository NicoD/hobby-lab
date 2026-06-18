<?php

declare(strict_types=1);

namespace App\Shared\Domain\Model;

use Symfony\Component\Uid\Uuid;

abstract readonly class AbstractUuid implements \Stringable
{
    final public function __construct(public string $uuid)
    {
    }

    final public static function create(): static
    {
        return new static(Uuid::v7()->toRfc4122());
    }

    #[\Override]
    final public function __toString(): string
    {
        return $this->uuid;
    }
}
