<?php

declare(strict_types=1);

namespace App\ColorLab\Application\Color\Query\GetColor;

use App\ColorLab\Application\Color\ReadModel\ColorReadRepository;
use App\ColorLab\Application\Color\ReadModel\ColorView;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class GetColorQueryHandler
{
    public function __construct(private ColorReadRepository $colors)
    {
    }

    public function __invoke(GetColorQuery $query): ?ColorView
    {
        return $this->colors->findByHandle($query->handle);
    }
}
