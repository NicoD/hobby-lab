<?php

declare(strict_types=1);

function str_cast(mixed $value): ?string
{
    return is_scalar($value) || $value instanceof Stringable ? (string) $value : null;
}

/**
 * @template T of object
 *
 * @param class-string<T> $class
 *
 * @return T|null
 */
function wrap(mixed $value, string $class): ?object
{
    if (null === $value) {
        return null;
    }
    assert((static function () use ($value, $class): bool {
        $params = new ReflectionClass($class)->getConstructor()?->getParameters() ?? [];

        if (1 !== count($params)) {
            return false;
        }

        $type = $params[0]->getType();
        $actual = get_debug_type($value);

        return match (true) {
            $type instanceof ReflectionNamedType => $actual === $type->getName(),
            $type instanceof ReflectionUnionType => in_array($actual, array_map(
                static fn (\ReflectionIntersectionType|ReflectionNamedType $t) => $t instanceof ReflectionNamedType ? $t->getName() : '',
                $type->getTypes(),
            ), true),
            default => false,
        };
    })());

    return new $class($value);
}
