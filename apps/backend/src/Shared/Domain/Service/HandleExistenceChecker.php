<?php

declare(strict_types=1);

namespace App\Shared\Domain\Service;

interface HandleExistenceChecker
{
    public function handleExists(string $handle): bool;
}
