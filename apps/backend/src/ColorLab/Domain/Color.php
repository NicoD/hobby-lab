<?php

declare(strict_types=1);

namespace App\ColorLab\Domain;

use App\ColorLab\Domain\ValueObject\BrandHandle;
use App\ColorLab\Domain\ValueObject\ColorId;
use App\Shared\Domain\ValueObject\UserId;
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

    #[ORM\Column(type: 'user_id')]
    public private(set) UserId $ownedBy;

    private function __construct(
        ColorId $id,
        string $name,
        BrandHandle $brandHandle,
        UserId $ownedBy,
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->brandHandle = $brandHandle;
        $this->ownedBy = $ownedBy;
    }

    public static function create(string $name, UserId $ownedBy): self
    {
        return new self(
            ColorId::create(),
            $name,
            BrandHandle::create($name),
            $ownedBy,
        );
    }
}
