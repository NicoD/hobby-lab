<?php

declare(strict_types=1);

namespace App\ColorLab\Application\Brand\ReadModel;

interface BrandReadRepository
{
    /** @return list<BrandListItemView> */
    public function list(): array;

    public function findByHandle(string $handle): ?BrandView;
}
