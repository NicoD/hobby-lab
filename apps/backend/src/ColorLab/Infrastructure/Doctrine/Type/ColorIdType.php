<?php

declare(strict_types=1);

namespace App\ColorLab\Infrastructure\Doctrine\Type;

use App\ColorLab\Domain\Model\ColorId;
use App\Shared\Infrastructure\Doctrine\Type\AbstractUuidType;

final class ColorIdType extends AbstractUuidType
{
    public const NAME = 'color_id';

    #[\Override]
    protected function getClass(): string
    {
        return ColorId::class;
    }
}
