<?php

declare(strict_types=1);

namespace App\ColorLab\Catalog\Application\PaintType\ReadModel;

interface PaintTypeReadRepository
{
    /** @return list<PaintTypeListItemView> */
    public function list(): array;

    public function findByHandle(string $handle): ?PaintTypeView;
}
