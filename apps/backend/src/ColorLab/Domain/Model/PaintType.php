<?php

declare(strict_types=1);

namespace App\ColorLab\Domain\Model;

use App\ColorLab\Domain\Event\PaintTypeCreatedEvent;
use App\Shared\Domain\Event\DomainEventTrait;
use App\Shared\Domain\Model\AggregateRoot;
use App\Shared\Domain\Model\UserId;
use App\Shared\Domain\Service\HandleGenerator;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'paint_types')]
class PaintType implements AggregateRoot
{
    use DomainEventTrait;

    private function __construct(
        #[ORM\Id]
        #[ORM\Column(type: 'paint_type_handle')]
        public private(set) PaintTypeHandle $handle,
        #[ORM\Column(type: 'string', length: 255)]
        public private(set) string $name,
        #[ORM\Column(type: 'user_id')]
        public private(set) UserId $ownedBy,
    ) {
    }

    public static function create(string $name, UserId $ownedBy, HandleGenerator $handleGenerator): self
    {
        $paintType = new self(
            new PaintTypeHandle($handleGenerator->generate($name)),
            $name,
            $ownedBy,
        );

        $paintType->raiseDomainEvent(new PaintTypeCreatedEvent($paintType->handle, $name, $ownedBy));

        return $paintType;
    }
}
