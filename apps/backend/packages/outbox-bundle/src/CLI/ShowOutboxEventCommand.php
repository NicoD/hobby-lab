<?php

declare(strict_types=1);

namespace Outbox\CLI;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'outbox:event', description: 'Show the full detail of a single outbox event')]
final class ShowOutboxEventCommand extends Command
{
    public function __construct(
        private readonly Connection $connection,
    ) {
        parent::__construct();
    }

    #[\Override]
    protected function configure(): void
    {
        $this->addArgument('id', InputArgument::REQUIRED, 'Event UUID');
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $row = $this->connection->fetchAssociative(
            'SELECT * FROM outbox_events WHERE id = :id',
            ['id' => $input->getArgument('id')],
        );

        if (false === $row) {
            $output->writeln('<error>Event not found.</error>');

            return Command::FAILURE;
        }

        /** @var array<string, string|null> $row */
        $row['domain_payload'] = json_encode(
            json_decode((string) $row['domain_payload'], true),
            \JSON_PRETTY_PRINT | \JSON_UNESCAPED_UNICODE | \JSON_THROW_ON_ERROR,
        );

        if (null !== $row['integration_payload']) {
            $row['integration_payload'] = json_encode(
                json_decode((string) $row['integration_payload'], true),
                \JSON_PRETTY_PRINT | \JSON_UNESCAPED_UNICODE | \JSON_THROW_ON_ERROR,
            );
        }

        foreach ($row as $key => $value) {
            $output->writeln(\sprintf('<info>%-16s</info> %s', $key, $value ?? '-'));
        }

        return Command::SUCCESS;
    }
}
