<?php

declare(strict_types=1);

namespace App\ColorLab\Application\Paint\Command\CreatePaint;

final readonly class CreatePaintCommand
{
    public function __construct(
        public string $paintReferenceId,
        public string $ownedBy,
        public ?string $purchasedAt,
    ) {
    }
}
