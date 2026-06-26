<?php

declare(strict_types=1);

namespace Outbox;

interface OutboxNotifier
{
    public function notify(): void;
}
