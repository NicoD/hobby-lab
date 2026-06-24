<?php

declare(strict_types=1);

namespace App\ColorLab\Catalog\Domain\Range;

use App\Shared\Domain\Service\HandleGenerator;

final readonly class Range
{
    private function __construct(public RangeHandle $handle, public string $name)
    {
    }

    public static function create(string $name, HandleGenerator $handleGenerator): self
    {
        return new self(
            new RangeHandle($handleGenerator->generate($name)),
            $name
        );
    }
}
