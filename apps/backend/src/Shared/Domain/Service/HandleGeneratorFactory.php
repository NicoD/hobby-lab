<?php

declare(strict_types=1);

namespace App\Shared\Domain\Service;

use App\Shared\Domain\Exception\DomainException;

final readonly class HandleGeneratorFactory
{
    public function __construct(private Slugifier $slugifier)
    {
    }

    public function create(HandleExistenceChecker $handleExistenceChecker): HandleGenerator
    {
        return new class($handleExistenceChecker, $this->slugifier) implements HandleGenerator {
            public function __construct(private HandleExistenceChecker $handleExistenceChecker, private Slugifier $slugifier)
            {
            }

            public function generate(string|\Stringable $name): string
            {
                for ($i = 0;; ++$i) {
                    if ($i >= 50) {
                        throw new DomainException('unable to generate slug');
                    }

                    $slug = $this->slugifier->slugify($name, $i);
                    if (!$this->handleExistenceChecker->handleExists($slug)) {
                        return $slug;
                    }
                }
            }
        };
    }
}
