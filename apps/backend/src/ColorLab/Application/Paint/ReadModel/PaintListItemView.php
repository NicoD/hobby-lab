<?php

declare(strict_types=1);

namespace App\ColorLab\Application\Paint\ReadModel;

final readonly class PaintListItemView
{
    public function __construct(
        public string $id,
        public string $paintReferenceId,
        public ?string $purchasedAt,
    ) {
    }
}
