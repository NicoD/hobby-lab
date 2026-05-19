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
    public private(set) ColorId $id;

    #[ORM\Column(type: 'string', length: 255)]
    public private(set) string $name;

    #[ORM\Column(type: 'brand_handle')]
    public private(set) BrandHandle $brandHandle;

    public function __construct(
        string $name,
        BrandHandle $brandHandle,
    ) {
        $this->id = ColorId::create();
        $this->name = $name;
        $this->brandHandle = $brandHandle;
    }
}
