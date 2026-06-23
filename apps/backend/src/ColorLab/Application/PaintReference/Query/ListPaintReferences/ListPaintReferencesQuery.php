<?php

declare(strict_types=1);

namespace App\ColorLab\Application\PaintReference\Query\ListPaintReferences;

use App\ColorLab\Application\PaintReference\ReadModel\PaintReferenceListItemView;
use App\Shared\Application\Bus\Query;

/** @implements Query<list<PaintReferenceListItemView>> */
final readonly class ListPaintReferencesQuery implements Query
{
    public function __construct(
        public string $ownedBy,
        public ?string $brandHandle = null,
        public ?string $rangeHandle = null,
        public ?string $paintTypeHandle = null,
        public ?string $colorHandle = null,
    ) {
    }
}
