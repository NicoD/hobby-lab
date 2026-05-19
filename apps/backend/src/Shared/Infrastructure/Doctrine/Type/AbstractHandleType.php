<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Doctrine\Type;

use App\Shared\Domain\ValueObject\AbstractHandle;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

abstract class AbstractHandleType extends Type
{
    /** @return class-string<AbstractHandle> */
    abstract protected function getClass(): string;

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return 'text';
    }

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if ($value instanceof AbstractHandle) {
            return (string) $value;
        }

        return is_string($value) ? $value : null;
    }

    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): mixed
    {
        if (!is_string($value)) {
            return null;
        }

        $class = $this->getClass();

        return $class::fromString($value);
    }
}
