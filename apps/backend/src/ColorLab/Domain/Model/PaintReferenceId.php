<?php

declare(strict_types=1);

namespace App\ColorLab\Domain\Model;

use App\Shared\Domain\Model\AbstractUuid;
use App\Shared\Domain\Model\AggregateRootId;

final readonly class PaintReferenceId extends AbstractUuid implements AggregateRootId
{
}
