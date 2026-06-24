<?php

declare(strict_types=1);

namespace App\ColorLab\Application\PaintReference\ReadModel;

final readonly class PaintReferenceListItemView
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
