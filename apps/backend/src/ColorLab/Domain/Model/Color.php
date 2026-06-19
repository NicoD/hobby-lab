<?php

declare(strict_types=1);

namespace App\ColorLab\Domain\Model;

use App\ColorLab\Domain\Event\ColorCreatedEvent;
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

    private function __construct(
        #[ORM\Id]
        #[ORM\Column(type: 'color_handle')]
        public private(set) ColorHandle $handle,
        #[ORM\Column(type: 'string', length: 255)]
        public private(set) string $name,
        #[ORM\Column(type: 'user_id')]
        public private(set) UserId $ownedBy,
    ) {
    }

    public static function create(string $name, UserId $ownedBy, HandleGenerator $handleGenerator): self
    {
        $color = new self(
            new ColorHandle($handleGenerator->generate($name)),
            $name,
            $ownedBy,
        );

        $color->raiseDomainEvent(new ColorCreatedEvent($color->handle, $name, $ownedBy));

        return $color;
    }
}
