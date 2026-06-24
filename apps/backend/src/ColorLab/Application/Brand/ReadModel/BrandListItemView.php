<?php

declare(strict_types=1);

namespace App\ColorLab\Application\Brand\ReadModel;

final readonly class BrandListItemView
{
    /**
     * @param list<array{handle: string, name: string}> $ranges
     */
    public function __construct(
        public string $handle,
        public string $name,
        public array $ranges,
        public string $createdAt,
    ) {
    }
}
