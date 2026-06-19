<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Doctrine;

use App\Shared\Application\Service\TransactionBoundary;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;

#[AsAlias(TransactionBoundary::class)]
final readonly class DoctrineTransactionBoundary implements TransactionBoundary
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    #[\Override]
    public function isTransactionActive(): bool
    {
        return $this->entityManager->getConnection()->isTransactionActive();
    }

    #[\Override]
    public function begin(): void
    {
        $this->entityManager->beginTransaction();
    }

    #[\Override]
    public function commit(): void
    {
        $this->entityManager->flush();
        $this->entityManager->commit();
    }

    #[\Override]
    public function rollback(): void
    {
        if ($this->entityManager->isOpen()) {
            $this->entityManager->rollback();
        }
    }
}
