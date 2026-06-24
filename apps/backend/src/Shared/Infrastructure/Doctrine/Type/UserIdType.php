<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Doctrine\Type;

use App\Identity\UserId;

final class UserIdType extends AbstractUuidType
{
    public const string NAME = 'user_id';

    #[\Override]
    protected function getClass(): string
    {
        return UserId::class;
    }
}
