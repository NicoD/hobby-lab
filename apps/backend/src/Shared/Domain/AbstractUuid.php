<?php

declare(strict_types=1);

namespace App\Shared\Domain\ValueObject;

use Symfony\Component\Uid\Uuid;

abstract readonly class AbstractUuid implements \Stringable {

    final private function __construct(public string $uuid) {
    }

    public static function fromString(string $uuid): static {
        return new static($uuid);
    }

    public static function create(): static {
        return new static(Uuid::v7()->toRfc4122());
    }

    final public function __toString(): string {
        return $this->uuid;
    }
}