<?php

declare(strict_types=1);

namespace App\ColorLab\Infrastructure\Doctrine\Type;

use App\ColorLab\Domain\Model\PaintId;
use App\Shared\Infrastructure\Doctrine\Type\AbstractUuidType;

final class PaintIdType extends AbstractUuidType
{
    public const NAME = 'paint_id';

    #[\Override]
    protected function getClass(): string
    {
        return PaintId::class;
    }
}
