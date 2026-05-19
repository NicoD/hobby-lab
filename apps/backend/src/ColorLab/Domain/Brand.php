<?php

declare(strict_types=1);

namespace App\ColorLab\Domain;

use App\ColorLab\Domain\ValueObject\BrandHandle;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'brands')]
class Brand
{
    #[ORM\Id]
    #[ORM\Column(type: 'brand_handle')]
    public private(set) BrandHandle $handle;

    #[ORM\Column(type: 'string', length: 255)]
    public private(set) string $name;

    public function __construct(
        BrandHandle $handle,
        string $name,
    ) {
        $this->name = $name;
        $this->handle = $handle;
    }


}
