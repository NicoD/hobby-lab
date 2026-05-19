<?php

declare(strict_types=1);

namespace App\ColorLab\Infrastructure\Doctrine\Type;

use App\ColorLab\Domain\ValueObject\ColorId;
use App\Shared\Infrastructure\Doctrine\Type\AbstractUuidType;

final class ColorIdType extends AbstractUuidType
{
    public const NAME = 'color_id';

    protected function getClass(): string
    {
        return ColorId::class;
    }
}
