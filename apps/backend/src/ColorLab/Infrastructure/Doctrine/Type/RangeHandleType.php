<?php

declare(strict_types=1);

namespace App\ColorLab\Infrastructure\Doctrine\Type;

use App\ColorLab\Domain\Model\RangeHandle;
use App\Shared\Infrastructure\Doctrine\Type\AbstractHandleType;

final class RangeHandleType extends AbstractHandleType
{
    public const string NAME = 'range_handle';

    #[\Override]
    protected function getClass(): string
    {
        return RangeHandle::class;
    }
}
