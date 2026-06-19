<?php

declare(strict_types=1);

namespace App\ColorLab\Infrastructure\Doctrine\Type;

use App\ColorLab\Domain\Model\Range;
use App\ColorLab\Domain\Model\RangeHandle;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\JsonType;

class RangeCollectionType extends JsonType
{
    #[\Override]
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return 'JSON';
    }

    #[\Override]
    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if (!\is_array($value)) {
            return null;
        }

        return parent::convertToDatabaseValue(array_map(
            static fn (\App\ColorLab\Domain\Model\Range $value): array => [
                'handle' => (string) $value->handle,
                'name' => $value->name,
            ],
            array_filter(
                $value,
                static fn ($v): bool => $v instanceof Range
            )
        ), $platform);
    }

    #[\Override]
    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): mixed
    {
        $value = parent::convertToPHPValue($value, $platform);
        if (!\is_array($value)) {
            return null;
        }

        $reflection = new \ReflectionClass(Range::class);

        return array_map(
            static function (mixed $data) use ($reflection) {
                /** @var array{handle: string, name: string} $data */
                $range = $reflection->newInstanceWithoutConstructor();

                $reflection->getProperty('name')->setValue($range, $data['name']);
                $reflection->getProperty('handle')->setValue($range, new RangeHandle((string) $data['handle']));

                return $range;
            },
            $value
        );
    }
}
