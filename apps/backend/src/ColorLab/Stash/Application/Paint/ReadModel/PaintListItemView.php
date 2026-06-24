<?php

declare(strict_types=1);

namespace App\ColorLab\Stash\Application\Paint\ReadModel;

final readonly class PaintListItemView
{
    public function __construct(
        public string $id,
        public string $paintHandle,
        public ?string $purchasedAt,
    ) {
    }
}
