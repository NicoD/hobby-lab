<?php

declare(strict_types=1);

namespace App\ColorLab\Infrastructure\Doctrine\Type;

use App\ColorLab\Domain\Model\PaintTypeHandle;
use App\Shared\Infrastructure\Doctrine\Type\AbstractHandleType;

final class PaintTypeHandleType extends AbstractHandleType
{
    public const NAME = 'paint_type_handle';

    #[\Override]
    protected function getClass(): string
    {
        return PaintTypeHandle::class;
    }
}
