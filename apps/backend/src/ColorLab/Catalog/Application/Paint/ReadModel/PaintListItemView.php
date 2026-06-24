<?php

declare(strict_types=1);

namespace App\ColorLab\Catalog\Application\Paint\ReadModel;

final readonly class PaintListItemView
{
    public function __construct(
        public string $handle,
        public string $name,
        public string $createdAt,
        public ?string $brandId,
        public ?string $brandName,
        public ?string $rangeId,
        public ?string $rangeName,
        public ?string $paintTypeId,
        public ?string $paintTypeName,
        public ?string $colorId,
        public ?string $colorName,
    ) {
    }
}
