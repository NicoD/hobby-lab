<?php

declare(strict_types=1);

namespace App\ColorLab\Application\Brand\Command\CreateBrand;

final readonly class CreateBrandCommand
{
    public function __construct(
        public string $name,
        public string $ownedBy,
    ) {
    }
}
