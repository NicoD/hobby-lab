<?php

declare(strict_types=1);

namespace App\ColorLab\Application\Paint\Query\GetPaint;

use App\ColorLab\Application\Paint\ReadModel\PaintView;
use App\Shared\Application\Bus\Query;

/** @implements Query<PaintView|null> */
final readonly class GetPaintQuery implements Query
{
    public function __construct(public string $id)
    {
    }
}
