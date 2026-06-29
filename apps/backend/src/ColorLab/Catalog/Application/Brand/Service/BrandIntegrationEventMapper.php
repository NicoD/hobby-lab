<?php

declare(strict_types=1);

namespace App\ColorLab\Catalog\Application\Brand\Service;

use App\Shared\Application\Service\IntegrationEventMapper;

final readonly class BrandIntegrationEventMapper implements IntegrationEventMapper
{
    #[\Override]
    public function supports(string $domainType): bool
    {
        return 'colorlab.brand.created' === $domainType;
    }

    #[\Override]
    public function map(array $domainPayload): array
    {
        return [
            'brand_handle' => $domainPayload['brand_handle'],
            'name' => $domainPayload['name'],
            'owned_by' => $domainPayload['owned_by'],
        ];
    }
}
