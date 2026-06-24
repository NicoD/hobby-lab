<?php

declare(strict_types=1);

namespace App\ColorLab\Catalog\Application\Brand\ReadModel;

final readonly class BrandView
{
    /**
     * @param list<array{handle: string, name: string}> $ranges
     */
    public function __construct(
        public string $handle,
        public string $name,
        public array $ranges,
    ) {
    }
}
