<?php

declare(strict_types=1);

namespace App\Shared\Application\Bus;

interface CommandBus
{
    /**
     * @template TResult
     *
     * @param Command<TResult> $command
     *
     * @return TResult
     */
    public function handle(object $command): mixed;
}
