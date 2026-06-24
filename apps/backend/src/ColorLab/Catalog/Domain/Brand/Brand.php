<?php

declare(strict_types=1);

namespace App\ColorLab\Catalog\Domain\Brand;

use App\ColorLab\Catalog\Domain\Brand\Event\BrandCreatedEvent;
use App\ColorLab\Catalog\Domain\Brand\Range\Range;
use App\Identity\UserId;
use App\Shared\Domain\Event\DomainEventTrait;
use App\Shared\Domain\Model\AggregateRoot;
use App\Shared\Domain\Service\HandleGenerator;
use App\Shared\Domain\Service\HandleGeneratorFactory;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'catalog_brands')]
class Brand implements AggregateRoot
{
    use DomainEventTrait;

    /** @var list<Range> */
    #[ORM\Column(type: 'catalog_range_collection')]
    public private(set) array $ranges = [];

    private function __construct(
        #[ORM\Id]
        #[ORM\Column(type: 'catalog_brand_handle')]
        public private(set) BrandHandle $handle,
        #[ORM\Column(type: 'string', length: 255)]
        public private(set) string $name,
        #[ORM\Column(type: 'user_id')]
        public private(set) UserId $ownedBy,
        #[ORM\Column(type: 'datetime_immutable')]
        public private(set) \DateTimeImmutable $createdAt = new \DateTimeImmutable(),
    ) {
    }

    public function addRange(string $name, HandleGeneratorFactory $handleGeneratorFactory): void
    {
        $existingHandles = array_map(static fn (Range $r): string => (string) $r->handle, $this->ranges);

        $this->ranges[] = Range::create(
            $name,
            $handleGeneratorFactory->create(
                static fn (string $h): bool => \in_array($h, $existingHandles, true)
            ),
        );
    }

    public static function create(string $name, UserId $ownedBy, HandleGenerator $handleGenerator): self
    {
        $brand = new self(
            new BrandHandle($handleGenerator->generate($name)),
            $name,
            $ownedBy,
        );

        $brand->raiseDomainEvent(new BrandCreatedEvent($brand->handle, $name, $ownedBy));

        return $brand;
    }
}
