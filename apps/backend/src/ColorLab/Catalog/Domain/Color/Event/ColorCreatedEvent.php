<?php

declare(strict_types=1);

namespace App\ColorLab\Catalog\Domain\Color\Event;

use App\ColorLab\Catalog\Domain\Color\ColorHandle;
use App\Identity\UserId;
use App\Shared\Domain\Event\DomainEvent;

final class ColorCreatedEvent extends DomainEvent
{
    public function __construct(
        ColorHandle $aggregateId,
        public readonly string $name,
        public readonly UserId $ownedBy,
    ) {
        parent::__construct($aggregateId);
    }

    public string $type { get => 'colorlab.color.created'; }

    /** @var array<string, scalar|null> */
    public array $payload {
        get => [
            'color_handle' => (string) $this->aggregateId,
            'name' => $this->name,
            'owned_by' => (string) $this->ownedBy,
        ];
    }
}
