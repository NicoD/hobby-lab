<?php

declare(strict_types=1);

namespace App\Shared\Domain\ValueObject;

abstract readonly class AbstractHandle implements \Stringable {

    final private function __construct(public string $handle) {
    }

    public static function fromString(string $uuid): static {
        return new static($uuid);
    }

    final public function __toString(): string {
        return $this->handle;
    }
}