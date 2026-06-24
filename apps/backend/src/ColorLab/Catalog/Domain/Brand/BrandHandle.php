<?php

declare(strict_types=1);

namespace App\ColorLab\Catalog\Domain\Brand;

use App\Shared\Domain\Model\AbstractHandle;
use App\Shared\Domain\Model\AggregateRootId;

final readonly class BrandHandle extends AbstractHandle implements AggregateRootId
{
}
