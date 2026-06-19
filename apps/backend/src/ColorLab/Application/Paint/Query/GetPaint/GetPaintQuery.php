<?php

declare(strict_types=1);

namespace App\ColorLab\Application\Paint\Query\GetPaint;

final readonly class GetPaintQuery
{
    public function __construct(public string $id)
    {
    }
}
