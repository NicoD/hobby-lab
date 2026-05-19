<?php

declare(strict_types=1);

namespace App\ColorLab\Infrastructure\Doctrine\Type;

use App\ColorLab\Domain\ValueObject\BrandHandle;
use App\Shared\Infrastructure\Doctrine\Type\AbstractHandleType;

final class BrandHandleType extends AbstractHandleType
{
    public const NAME = 'handle_id';

    protected function getClass(): string
    {
        return BrandHandle::class;
    }
}
