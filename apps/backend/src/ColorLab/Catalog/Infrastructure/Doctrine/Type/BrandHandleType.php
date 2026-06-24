<?php

declare(strict_types=1);

namespace App\ColorLab\Catalog\Infrastructure\Doctrine\Type;

use App\ColorLab\Catalog\Domain\Brand\BrandHandle;
use App\Shared\Infrastructure\Doctrine\Type\AbstractHandleType;

final class BrandHandleType extends AbstractHandleType
{
    public const string NAME = 'catalog_brand_handle';

    #[\Override]
    protected function getClass(): string
    {
        return BrandHandle::class;
    }
}
