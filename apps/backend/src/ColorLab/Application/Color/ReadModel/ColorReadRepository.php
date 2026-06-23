<?php

declare(strict_types=1);

namespace App\ColorLab\Application\Color\ReadModel;

interface ColorReadRepository
{
    /** @return list<ColorListItemView> */
    public function list(?string $search = null): array;

    public function findByHandle(string $handle): ?ColorView;
}
