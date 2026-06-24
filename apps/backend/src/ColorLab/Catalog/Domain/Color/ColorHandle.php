<?php

declare(strict_types=1);

namespace App\ColorLab\Catalog\Domain\Color;

use App\Shared\Domain\Model\AbstractHandle;
use App\Shared\Domain\Model\AggregateRootId;

final readonly class ColorHandle extends AbstractHandle implements AggregateRootId
{
}
