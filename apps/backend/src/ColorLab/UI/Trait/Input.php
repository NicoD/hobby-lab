<?php

declare(strict_types=1);

namespace App\ColorLab\UI\Trait;

trait Input
{
    public static function asString(mixed $value, string $default = ''): string
    {
        return (\is_scalar($value) || $value instanceof \Stringable) ? (string) $value : $default;
    }
}
