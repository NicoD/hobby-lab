<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Doctrine\Type;

use App\Shared\Domain\Model\UserId;

final class UserIdType extends AbstractUuidType
{
    public const NAME = 'user_id';

    protected function getClass(): string
    {
        return UserId::class;
    }
}
