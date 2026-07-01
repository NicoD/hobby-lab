<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Correlation;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;

final readonly class CorrelationMonologProcessor implements ProcessorInterface
{
    public function __construct(private CorrelationContext $correlationContext)
    {
    }

    #[\Override]
    public function __invoke(LogRecord $record): LogRecord
    {
        $correlationId = $this->correlationContext->get();
        if (null === $correlationId) {
            return $record;
        }

        return $record->with(extra: [...$record->extra, 'correlationId' => $correlationId]);
    }
}
