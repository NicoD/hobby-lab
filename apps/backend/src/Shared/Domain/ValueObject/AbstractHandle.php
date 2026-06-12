<?php

declare(strict_types=1);

namespace App\Shared\Domain\ValueObject;

use App\Shared\Domain\Trait\Slugify;

abstract readonly class AbstractHandle implements \Stringable
{
    use Slugify;

    final public function __construct(public string $handle)
    {
    }

    final public static function create(string $label): static
    {
        return new static(static::slugify($label));
    }

    final public function __toString(): string
    {
        return $this->handle;
    }
}
