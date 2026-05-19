<?php

declare(strict_types=1);

namespace App\ColorLab\Domain;

use App\ColorLab\Domain\ValueObject\BrandHandle;
use App\ColorLab\Domain\ValueObject\ColorId;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'brands')]
class Brand
{
    #[ORM\Id]
    #[ORM\Column(type: 'brand_handle')]
    private BrandHandle $handle;


    public function __construct(
        BrandHandle $handle,
        string $name,
    ) {
        $this->name = $name;
        $this->handle = $handle;
    }
    
    #[ORM\Column(type: 'string', length: 255)]
    public string $name {
        get => $this->name;
    }
}
