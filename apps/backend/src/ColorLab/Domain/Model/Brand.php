<?php

declare(strict_types=1);

namespace App\ColorLab\Domain\Model;

use App\Shared\Domain\Model\UserId;
use App\Shared\Domain\Service\HandleExistenceChecker;
use App\Shared\Domain\Service\HandleGenerator;
use App\Shared\Domain\Service\HandleGeneratorFactory;
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

    private function __construct(
        BrandHandle $handle,
        string $name,
        UserId $ownedBy,
    ) {
        $this->handle = $handle;
        $this->name = $name;
        $this->ownedBy = $ownedBy;
        $this->ranges = [];
    }

    public function addRange(string $name, HandleGeneratorFactory $handleGeneratorFactory): void
    {
        $existingHandles = array_map(static fn (Range $r) => (string) $r->handle, $this->ranges);

        $handleGenerator = $handleGeneratorFactory->create(
            new class($existingHandles)implements HandleExistenceChecker {
                /** @param list<string> $handles */
                public function __construct(private array $handles)
                {
                }

                public function handleExists(string $handle): bool
                {
                    return \in_array($handle, $this->handles, true);
                }
            }
        );

        $this->ranges[] = Range::create($name, $handleGenerator);
    }

    public static function create(string $name, UserId $ownedBy, HandleGenerator $handleGenerator): self
    {
        return new self(
            new BrandHandle($handleGenerator->generate($name)),
            $name,
            $ownedBy,
        );
    }
}
