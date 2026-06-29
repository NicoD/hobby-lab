<?php

declare(strict_types=1);

namespace App\ColorLab\Catalog\Domain\PaintType\Event;

use App\ColorLab\Catalog\Domain\PaintType\PaintTypeHandle;
use App\Identity\UserId;
use App\Shared\Domain\Event\DomainEvent;

final class PaintTypeCreatedEvent extends DomainEvent
{
    public function __construct(
        PaintTypeHandle $aggregateId,
        public readonly string $name,
        public readonly UserId $ownedBy,
    ) {
        parent::__construct($aggregateId);
    }

    public string $type { get => 'colorlab.paint_type.created'; }

    /** @var array<string, scalar|null> */
    public array $payload {
        get => [
            'paint_type_handle' => (string) $this->aggregateId,
            'name' => $this->name,
            'owned_by' => (string) $this->ownedBy,
        ];
    }
}
