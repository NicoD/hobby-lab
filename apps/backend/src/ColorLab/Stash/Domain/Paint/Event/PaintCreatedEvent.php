<?php

declare(strict_types=1);

namespace App\ColorLab\Stash\Domain\Paint\Event;

use App\ColorLab\Catalog\Domain\Paint\PaintHandle;
use App\ColorLab\Stash\Domain\Paint\PaintId;
use App\Identity\UserId;
use App\Shared\Domain\Event\DomainEvent;

final class PaintCreatedEvent extends DomainEvent
{
    public function __construct(
        PaintId $aggregateId,
        public readonly PaintHandle $paintHandle,
        public readonly UserId $ownedBy,
        public readonly ?\DateTimeImmutable $purchasedAt,
    ) {
        parent::__construct($aggregateId);
    }

    public string $type { get => 'colorlab.stash.paint.created'; }

    /** @var array<string, scalar|null> */
    public array $payload {
        get => [
            'paint_id' => (string) $this->aggregateId,
            'paint_handle' => (string) $this->paintHandle,
            'owned_by' => (string) $this->ownedBy,
            'purchased_at' => $this->purchasedAt?->format(\DateTimeInterface::ATOM),
        ];
    }
}
