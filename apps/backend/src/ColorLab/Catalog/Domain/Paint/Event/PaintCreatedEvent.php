<?php

declare(strict_types=1);

namespace App\ColorLab\Catalog\Domain\Paint\Event;

use App\ColorLab\Catalog\Domain\Brand\BrandHandle;
use App\ColorLab\Catalog\Domain\Brand\Range\RangeHandle;
use App\ColorLab\Catalog\Domain\Color\ColorHandle;
use App\ColorLab\Catalog\Domain\Paint\PaintHandle;
use App\ColorLab\Catalog\Domain\PaintType\PaintTypeHandle;
use App\Identity\UserId;
use App\Shared\Domain\Event\DomainEvent;

final class PaintCreatedEvent extends DomainEvent
{
    public function __construct(
        PaintHandle $aggregateId,
        public readonly string $name,
        public readonly ?BrandHandle $brandHandle,
        public readonly ?RangeHandle $rangeHandle,
        public readonly ?PaintTypeHandle $paintTypeHandle,
        public readonly ?ColorHandle $colorHandle,
        public readonly UserId $userId,
    ) {
        parent::__construct($aggregateId);
    }

    public string $type { get => 'colorlab.catalog.paint.created'; }

    /** @var array<string, scalar|null> */
    public array $payload {
        get => [
            'paint_handle' => (string) $this->aggregateId,
            'name' => $this->name,
            'brand_handle' => $this->brandHandle instanceof BrandHandle ? (string) $this->brandHandle : null,
            'range_handle' => $this->rangeHandle instanceof RangeHandle ? (string) $this->rangeHandle : null,
            'paint_type_handle' => $this->paintTypeHandle instanceof PaintTypeHandle ? (string) $this->paintTypeHandle : null,
            'color_handle' => $this->colorHandle instanceof ColorHandle ? (string) $this->colorHandle : null,
            'user_id' => (string) $this->userId,
        ];
    }
}
