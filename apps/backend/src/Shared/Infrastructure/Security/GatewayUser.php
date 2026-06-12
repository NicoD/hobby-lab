<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Security;

use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Represents a user authenticated by the API gateway.
 * Built from X-User-Id / X-User-Roles headers — no JWT, no PII.
 */
final readonly class GatewayUser implements UserInterface
{
    /**
     * @param non-empty-string $userId
     * @param list<string>     $roles
     */
    public function __construct(
        private string $userId,
        private array $roles,
    ) {
    }

    public function getUserIdentifier(): string
    {
        return $this->userId;
    }

    /** @return list<string> */
    public function getRoles(): array
    {
        return $this->roles ?: ['ROLE_USER'];
    }

    public function eraseCredentials(): void
    {
    }
}
