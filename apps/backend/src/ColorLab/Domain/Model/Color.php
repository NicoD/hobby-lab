<?php

declare(strict_types=1);

namespace App\ColorLab\Domain\Model;

use App\Shared\Domain\Event\DomainEventTrait;
use App\Shared\Domain\Model\AggregateRoot;
use App\Shared\Domain\Model\UserId;
use App\Shared\Domain\Service\HandleGenerator;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'colors')]
class Color implements AggregateRoot
{
    use DomainEventTrait;

    #[ORM\Id]
    #[ORM\Column(type: 'color_id')]
    public private(set) ColorId $id;

    #[ORM\Column(type: 'string', length: 255)]
    public private(set) string $name;

    #[ORM\Column(type: 'color_handle')]
    public private(set) ColorHandle $colorHandle;

    #[ORM\Column(type: 'user_id')]
    public private(set) UserId $ownedBy;

    private function __construct(
        ColorId $id,
        string $name,
        ColorHandle $colorHandle,
        UserId $ownedBy,
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->colorHandle = $colorHandle;
        $this->ownedBy = $ownedBy;
    }

    public static function create(string $name, UserId $ownedBy, HandleGenerator $handleGenerator): self
    {
        return new self(
            ColorId::create(),
            $name,
            new ColorHandle($handleGenerator->generate($name)),
            $ownedBy,
        );
    }
}
