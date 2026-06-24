<?php

declare(strict_types=1);

namespace App\ColorLab\Catalog\Domain\PaintType;

use App\Shared\Domain\Model\AbstractHandle;
use App\Shared\Domain\Model\AggregateRootId;

final readonly class PaintTypeHandle extends AbstractHandle implements AggregateRootId
{
}
