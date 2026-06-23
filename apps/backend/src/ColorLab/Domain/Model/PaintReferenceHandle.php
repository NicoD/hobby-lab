<?php

declare(strict_types=1);

namespace App\ColorLab\Domain\Model;

use App\Shared\Domain\Model\AbstractHandle;
use App\Shared\Domain\Model\AggregateRootId;

final readonly class PaintReferenceHandle extends AbstractHandle implements AggregateRootId
{
}
