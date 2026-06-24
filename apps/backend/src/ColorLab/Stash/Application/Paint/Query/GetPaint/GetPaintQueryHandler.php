<?php

declare(strict_types=1);

namespace App\ColorLab\Stash\Application\Paint\Query\GetPaint;

use App\ColorLab\Stash\Application\Paint\ReadModel\PaintReadRepository;
use App\ColorLab\Stash\Application\Paint\ReadModel\PaintView;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class GetPaintQueryHandler
{
    public function __construct(private PaintReadRepository $paints)
    {
    }

    public function __invoke(GetPaintQuery $query): ?PaintView
    {
        return $this->paints->findById($query->id);
    }
}
