<?php

declare(strict_types=1);

namespace App\ColorLab\Infrastructure\Doctrine\Type;

use App\ColorLab\Domain\Model\ColorHandle;
use App\Shared\Infrastructure\Doctrine\Type\AbstractHandleType;

final class ColorHandleType extends AbstractHandleType
{
    public const NAME = 'color_handle';

    #[\Override]
    protected function getClass(): string
    {
        return ColorHandle::class;
    }
}
