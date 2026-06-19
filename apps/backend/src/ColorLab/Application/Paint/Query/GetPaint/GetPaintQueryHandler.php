<?php

declare(strict_types=1);

namespace App\ColorLab\Application\Paint\Query\GetPaint;

use App\ColorLab\Application\Paint\ReadModel\PaintReadRepository;
use App\ColorLab\Application\Paint\ReadModel\PaintView;

final class GetPaintQueryHandler
{
    public function __construct(private readonly PaintReadRepository $paints)
    {
    }

    public function __invoke(GetPaintQuery $query): ?PaintView
    {
        return $this->paints->findById($query->id);
    }
}
