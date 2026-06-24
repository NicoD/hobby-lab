<?php

declare(strict_types=1);

namespace App\ColorLab\Stash\Infrastructure\Doctrine\Type;

use App\ColorLab\Stash\Domain\Paint\PaintId;
use App\Shared\Infrastructure\Doctrine\Type\AbstractUuidType;

final class PaintIdType extends AbstractUuidType
{
    public const string NAME = 'stash_paint_id';

    #[\Override]
    protected function getClass(): string
    {
        return PaintId::class;
    }
}
