<?php

declare(strict_types=1);

namespace App\ColorLab\Catalog\Infrastructure\Doctrine\Type;

use App\ColorLab\Catalog\Domain\Brand\Range\RangeHandle;
use App\Shared\Infrastructure\Doctrine\Type\AbstractHandleType;

final class RangeHandleType extends AbstractHandleType
{
    public const string NAME = 'catalog_range_handle';

    #[\Override]
    protected function getClass(): string
    {
        return RangeHandle::class;
    }
}
