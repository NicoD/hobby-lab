<?php

declare(strict_types=1);

namespace App\ColorLab\Domain\Event;

use App\ColorLab\Domain\Model\BrandHandle;
use App\ColorLab\Domain\Model\ColorHandle;
use App\ColorLab\Domain\Model\PaintReferenceHandle;
use App\ColorLab\Domain\Model\PaintReferenceId;
use App\ColorLab\Domain\Model\PaintTypeHandle;
use App\ColorLab\Domain\Model\RangeHandle;
use App\Shared\Domain\Event\DomainEvent;
use App\Shared\Domain\Model\UserId;

final class PaintReferenceCreatedEvent extends DomainEvent
{
    public function __construct(
        PaintReferenceId $aggregateId,
        public readonly PaintReferenceHandle $handle,
        public readonly string $name,
        public readonly ?BrandHandle $brandHandle,
        public readonly ?RangeHandle $rangeHandle,
        public readonly ?PaintTypeHandle $paintTypeHandle,
        public readonly ?ColorHandle $colorHandle,
        public readonly UserId $ownedBy,
    ) {
        parent::__construct($aggregateId);
    }
}
