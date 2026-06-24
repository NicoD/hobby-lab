<?php

declare(strict_types=1);

namespace App\ColorLab\Catalog\Infrastructure\Doctrine\Type;

use App\ColorLab\Catalog\Domain\Paint\PaintHandle;
use App\Shared\Infrastructure\Doctrine\Type\AbstractHandleType;

final class PaintHandleType extends AbstractHandleType
{
    public const string NAME = 'catalog_paint_handle';

    #[\Override]
    protected function getClass(): string
    {
        return PaintHandle::class;
    }
}
