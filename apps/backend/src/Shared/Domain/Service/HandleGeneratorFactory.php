<?php

declare(strict_types=1);

namespace App\Shared\Domain\Service;

use App\Shared\Domain\Exception\DomainException;

final readonly class HandleGeneratorFactory
{
    public function __construct(private Slugifier $slugifier)
    {
    }

    /** @param \Closure(string): bool $existenceChecker */
    public function create(\Closure $existenceChecker): HandleGenerator
    {
        return new readonly class($existenceChecker, $this->slugifier) implements HandleGenerator {
            /** @param \Closure(string): bool $existenceChecker */
            public function __construct(private \Closure $existenceChecker, private Slugifier $slugifier)
            {
            }

            public function generate(string|\Stringable $name): string
            {
                for ($i = 0;; ++$i) {
                    if ($i >= 50) {
                        throw new DomainException('unable to generate handle');
                    }

                    $slug = $this->slugifier->slugify($name, $i);
                    if (!($this->existenceChecker)($slug)) {
                        return $slug;
                    }
                }
            }
        };
    }
}
