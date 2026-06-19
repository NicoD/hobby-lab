<?php

declare(strict_types=1);

namespace App\Shared\Domain\Service;

final readonly class Slugifier
{
    public function slugify(string|\Stringable $label, int $index): string
    {
        $slug = mb_strtolower((string) $label, 'UTF-8');
        $slug = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $slug) ?: $slug;
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);

        $slug = trim((string) $slug, '-');

        if (0 !== $index) {
            return \sprintf('%s-%d', $slug, $index);
        }

        return $slug;
    }
}
