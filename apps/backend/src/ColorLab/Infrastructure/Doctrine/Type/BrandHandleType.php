<?php

declare(strict_types=1);

namespace App\ColorLab\Infrastructure\Doctrine\Type;

use App\ColorLab\Domain\Model\BrandHandle;
use App\Shared\Infrastructure\Doctrine\Type\AbstractHandleType;

final class BrandHandleType extends AbstractHandleType
{
    public const NAME = 'brand_handle';

    protected function getClass(): string
    {
        return BrandHandle::class;
    }
}
