<?php

declare(strict_types=1);

namespace App\ColorLab\Application\Paint\ReadModel;

final readonly class PaintView
{
    public function __construct(
        public string $id,
        public string $paintReferenceHandle,
        public ?string $purchasedAt,
    ) {
    }
}
