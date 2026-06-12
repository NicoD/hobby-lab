<?php

declare(strict_types=1);

namespace App\Shared\Domain\Trait;

trait Slugify
{
    public static function slugify(string|\Stringable $label): string
    {
        $slug = mb_strtolower((string) $label, 'UTF-8');
        $slug = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $slug) ?: $slug;
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);

        return trim((string) $slug, '-');
    }
}
