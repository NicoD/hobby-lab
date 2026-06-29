<?php

declare(strict_types=1);

namespace App\Shared\Application\Service;

use Outbox\Publisher\IntegrationEventResolverInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

final readonly class IntegrationEventResolver implements IntegrationEventResolverInterface
{
    /** @var list<IntegrationEventMapper> */
    private array $mappers;

    /**
     * @param iterable<IntegrationEventMapper> $mappers
     */
    public function __construct(
        #[AutowireIterator('app.outbox.integration_event_mapper')]
        iterable $mappers,
    ) {
        $this->mappers = array_values(iterator_to_array($mappers));
    }

    #[\Override]
    public function resolve(string $domainType, array $domainPayload): ?array
    {
        foreach ($this->mappers as $mapper) {
            if ($mapper->supports($domainType)) {
                return $mapper->map($domainPayload);
            }
        }

        return null;
    }
}
