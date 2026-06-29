<?php

declare(strict_types=1);

namespace App\ColorLab\Catalog\Domain\Brand\Event;

use App\ColorLab\Catalog\Domain\Brand\BrandHandle;
use App\Identity\UserId;
use App\Shared\Domain\Event\DomainEvent;

final class BrandCreatedEvent extends DomainEvent
{
    public function __construct(
        BrandHandle $aggregateId,
        public readonly string $name,
        public readonly UserId $ownedBy,
    ) {
        parent::__construct($aggregateId);
    }

    public string $type { get => 'colorlab.brand.created'; }

    /** @var array<string, scalar|null> */
    public array $payload {
        get => [
            'brand_handle' => (string) $this->aggregateId,
            'name' => $this->name,
            'owned_by' => (string) $this->ownedBy,
        ];
    }
}
