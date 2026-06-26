<?php

declare(strict_types=1);

namespace Outbox;

interface OutboxRecorder
{
    public function record(OutboxMessage ...$messages): void;
}
