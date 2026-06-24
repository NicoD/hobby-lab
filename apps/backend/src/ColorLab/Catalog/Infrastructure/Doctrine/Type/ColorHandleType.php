<?php

declare(strict_types=1);

namespace App\ColorLab\Catalog\Infrastructure\Doctrine\Type;

use App\ColorLab\Catalog\Domain\Color\ColorHandle;
use App\Shared\Infrastructure\Doctrine\Type\AbstractHandleType;

final class ColorHandleType extends AbstractHandleType
{
    public const string NAME = 'catalog_color_handle';

    #[\Override]
    protected function getClass(): string
    {
        return ColorHandle::class;
    }
}
