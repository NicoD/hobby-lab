<?php

declare(strict_types=1);

namespace App\ColorLab\Domain\Model;

use App\Shared\Domain\Service\HandleGenerator;

final readonly class Range
{
    public RangeHandle $handle;
    public string $name;

    private function __construct(RangeHandle $handle, string $name)
    {
        $this->handle = $handle;
        $this->name = $name;
    }

    public static function create(string $name, HandleGenerator $handleGenerator): self
    {
        return new self(
            new RangeHandle($handleGenerator->generate($name)),
            $name
        );
    }
}
