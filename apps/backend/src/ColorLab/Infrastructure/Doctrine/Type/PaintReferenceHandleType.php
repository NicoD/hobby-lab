<?php

declare(strict_types=1);

namespace App\ColorLab\Infrastructure\Doctrine\Type;

use App\ColorLab\Domain\Model\PaintReferenceHandle;
use App\Shared\Infrastructure\Doctrine\Type\AbstractHandleType;

final class PaintReferenceHandleType extends AbstractHandleType
{
    public const NAME = 'paint_reference_handle';

    #[\Override]
    protected function getClass(): string
    {
        return PaintReferenceHandle::class;
    }
}
