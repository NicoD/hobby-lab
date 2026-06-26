<?php

declare(strict_types=1);

namespace Outbox\CLI;

use Outbox\Worker\OutboxWorker;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'outbox:process', description: 'Start the outbox worker')]
final class ProcessOutboxCommand extends Command
{
    public function __construct(
        private readonly OutboxWorker $worker,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Outbox worker started.');
        $this->worker->run();

        return Command::SUCCESS;
    }
}
