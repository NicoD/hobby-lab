<?php

declare(strict_types=1);

namespace App\Tests\Application;

use App\Application\FooBar;
use PHPUnit\Framework\TestCase;

class FooBarTest extends TestCase
{
    public function testBuzz(): void
    {
        $fooBar = new FooBar();
        $this->assertTrue($fooBar->buzz());
    }
}
