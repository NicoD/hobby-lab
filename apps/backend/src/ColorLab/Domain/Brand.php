<?php

declare(strict_types=1);

namespace App\ColorLab\Domain;

use App\ColorLab\Domain\ValueObject\BrandHandle;
use App\Shared\Domain\ValueObject\UserId;
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

    #[ORM\Column(type: 'user_id')]
    public private(set) UserId $ownedBy;

    /** @var list<Range> */
    #[ORM\Column(type: 'range_collection')]
    public private(set) array $ranges;

    /**
     * @param list<Range> $ranges
     */
    private function __construct(
        BrandHandle $handle,
        string $name,
        UserId $ownedBy,
        array $ranges,
    ) {
        $this->handle = $handle;
        $this->name = $name;
        $this->ownedBy = $ownedBy;
        $this->ranges = $ranges;
    }

    /**
     * @param list<Range> $ranges
     */
    public static function create(string $name, UserId $ownedBy, array $ranges): self
    {
        return new self(
            BrandHandle::create($name),
            $name,
            $ownedBy,
            $ranges,
        );
    }
}
