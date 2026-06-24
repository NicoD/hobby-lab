<?php

declare(strict_types=1);

namespace App\ColorLab\Catalog\Infrastructure\Doctrine\Type;

use App\ColorLab\Catalog\Domain\PaintType\PaintTypeHandle;
use App\Shared\Infrastructure\Doctrine\Type\AbstractHandleType;

final class PaintTypeHandleType extends AbstractHandleType
{
    public const string NAME = 'catalog_paint_type_handle';

    #[\Override]
    protected function getClass(): string
    {
        return PaintTypeHandle::class;
    }
}
