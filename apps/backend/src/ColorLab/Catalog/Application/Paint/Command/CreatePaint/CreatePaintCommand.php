<?php

declare(strict_types=1);

namespace App\ColorLab\Catalog\Application\Paint\Command\CreatePaint;

use App\ColorLab\Catalog\Domain\Paint\PaintHandle;
use App\Shared\Application\Bus\Command;

/** @implements Command<PaintHandle> */
final readonly class CreatePaintCommand implements Command
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
