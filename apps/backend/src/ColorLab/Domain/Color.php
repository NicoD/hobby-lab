<?php

declare(strict_types=1);

namespace App\ColorLab\Domain;

use App\ColorLab\Domain\ValueObject\BrandHandle;
use App\ColorLab\Domain\ValueObject\ColorId;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'colors')]
class Color
{
    #[ORM\Id]
    #[ORM\Column(type: 'color_id')]
    private ColorId $id;

    #[ORM\Column(type: 'string', length: 255)]
    public string $name {
        get => $this->name;
    }

    #[ORM\Column(type: 'brand_handle')]
    public BrandHandle $brandHandle {
        get => $this->brandHandle;
    }

    public function __construct(
        string $name,
        BrandHandle $brand
    ) {
        $this->id = ColorId::create();
        $this->name = $name;
        $this->brandHandle = $brandHandle;
    }
}
