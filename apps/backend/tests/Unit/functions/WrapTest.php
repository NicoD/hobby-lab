<?php

declare(strict_types=1);

namespace Test\Unit\functions;

use PHPUnit\Framework\TestCase;

class WrapTest extends TestCase
{
    protected function setUp(): void
    {
        ini_set('zend.assertions', '1');
        ini_set('assert.exception', '1');
    }

    public function testReturnsNullWhenValueIsNull(): void
    {
        self::assertNull(wrap(null, SingleStringParam::class));
    }

    public function testWrapsStringValue(): void
    {
        self::assertInstanceOf(SingleStringParam::class, wrap('hello', SingleStringParam::class));
    }

    public function testWrapsIntForUnionType(): void
    {
        self::assertInstanceOf(UnionIntOrStringParam::class, wrap(42, UnionIntOrStringParam::class));
    }

    public function testWrapsStringForUnionType(): void
    {
        self::assertInstanceOf(UnionIntOrStringParam::class, wrap('hello', UnionIntOrStringParam::class));
    }

    public function testAssertFailsOnWrongType(): void
    {
        $this->expectException(\AssertionError::class);
        wrap(42, SingleStringParam::class);
    }

    public function testAssertFailsOnWrongTypeForUnion(): void
    {
        $this->expectException(\AssertionError::class);
        wrap(3.14, UnionIntOrStringParam::class);
    }

    public function testAssertFailsWhenConstructorHasNoParams(): void
    {
        $this->expectException(\AssertionError::class);
        wrap('hello', NoParam::class);
    }

    public function testAssertFailsWhenConstructorHasMultipleParams(): void
    {
        $this->expectException(\AssertionError::class);
        wrap('hello', MultiParam::class);
    }

    public function testAssertFailsWhenParamIsUntyped(): void
    {
        $this->expectException(\AssertionError::class);
        wrap('hello', UntypedParam::class);
    }
}

// Fixtures

final readonly class SingleStringParam
{
    public function __construct(public string $value)
    {
    }
}

final readonly class UnionIntOrStringParam
{
    public function __construct(public int|string $value)
    {
    }
}

final class NoParam
{
}

final readonly class MultiParam
{
    public function __construct(
        public string $a,
        public string $b,
    ) {
    }
}

final class UntypedParam
{
}
