<?php

declare(strict_types=1);

namespace App\ColorLab\Stash\Domain\Paint;

use App\Shared\Domain\Model\AbstractUuid;
use App\Shared\Domain\Model\AggregateRootId;

final readonly class PaintId extends AbstractUuid implements AggregateRootId
{
}
