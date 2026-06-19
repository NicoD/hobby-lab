<?php

declare(strict_types=1);

namespace App\ColorLab\Application\PaintReference\ReadModel;

interface PaintReferenceReadRepository
{
    /** @return list<PaintReferenceListItemView> */
    public function list(): array;

    public function findByHandle(string $handle): ?PaintReferenceView;
}
