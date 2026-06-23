<?php

declare(strict_types=1);

namespace App\ColorLab\Application\PaintReference\Command\CreatePaintReference;

use App\ColorLab\Domain\Model\PaintReferenceHandle;
use App\Shared\Application\Bus\Command;

/** @implements Command<PaintReferenceHandle> */
final readonly class CreatePaintReferenceCommand implements Command
{
    public function __construct(
        public string $name,
        public ?string $brandHandle,
        public ?string $rangeHandle,
        public ?string $paintTypeHandle,
        public ?string $colorHandle,
        public string $ownedBy,
    ) {
    }
}
