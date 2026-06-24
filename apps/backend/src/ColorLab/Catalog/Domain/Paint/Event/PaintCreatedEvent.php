<?php

declare(strict_types=1);

namespace App\ColorLab\Catalog\Domain\Paint\Event;

use App\ColorLab\Catalog\Domain\Brand\BrandHandle;
use App\ColorLab\Catalog\Domain\Color\ColorHandle;
use App\ColorLab\Catalog\Domain\Paint\PaintHandle;
use App\ColorLab\Catalog\Domain\PaintType\PaintTypeHandle;
use App\ColorLab\Catalog\Domain\Range\RangeHandle;
use App\Shared\Domain\Event\DomainEvent;
use App\Shared\Domain\Model\UserId;

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
}
