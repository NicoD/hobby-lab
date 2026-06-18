<?php

declare(strict_types=1);

namespace App\Shared\Domain\Model;

abstract readonly class AbstractHandle implements \Stringable
{
    final public function __construct(public string $handle)
    {
    }

    #[\Override]
    final public function __toString(): string
    {
        return $this->handle;
    }
}
