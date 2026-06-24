<?php

declare(strict_types=1);

namespace App\ColorLab\Stash\Application\Paint\ReadModel;

interface PaintReadRepository
{
    /** @return list<PaintListItemView> */
    public function list(): array;

    public function findById(string $id): ?PaintView;
}
