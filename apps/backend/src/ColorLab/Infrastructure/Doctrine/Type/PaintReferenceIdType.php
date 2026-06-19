<?php

declare(strict_types=1);

namespace App\ColorLab\Infrastructure\Doctrine\Type;

use App\ColorLab\Domain\Model\PaintReferenceId;
use App\Shared\Infrastructure\Doctrine\Type\AbstractUuidType;

final class PaintReferenceIdType extends AbstractUuidType
{
    public const NAME = 'paint_reference_id';

    #[\Override]
    protected function getClass(): string
    {
        return PaintReferenceId::class;
    }
}
