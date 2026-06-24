<?php

declare(strict_types=1);

namespace App\ColorLab\Catalog\Application\Brand\Command\CreateBrand;

use App\ColorLab\Catalog\Domain\Brand\BrandHandle;
use App\Shared\Application\Bus\Command;

/** @implements Command<BrandHandle> */
final readonly class CreateBrandCommand implements Command
{
    public function __construct(
        public string $name,
        public string $ownedBy,
    ) {
    }
}
