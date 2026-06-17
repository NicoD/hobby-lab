<?php

declare(strict_types=1);

namespace App\Shared\Domain\Service;

interface HandleGenerator
{
    public function generate(string|\Stringable $name): string;
}
