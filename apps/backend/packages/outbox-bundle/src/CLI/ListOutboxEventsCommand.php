<?php

declare(strict_types=1);

namespace Outbox\CLI;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'outbox:events', description: 'List outbox events')]
final class ListOutboxEventsCommand extends Command
{
    public function __construct(
        private readonly Connection $connection,
    ) {
        parent::__construct();
    }

    #[\Override]
    protected function configure(): void
    {
        $this
            ->addOption('status', 's', InputOption::VALUE_REQUIRED, 'Filter by status: pending, sent, failed')
            ->addOption('type', 't', InputOption::VALUE_REQUIRED, 'Filter by event type')
            ->addOption('limit', 'l', InputOption::VALUE_REQUIRED, 'Number of events to display', 20)
            ->addOption('watch', 'w', InputOption::VALUE_NONE, 'Auto-refresh every 2 seconds (Ctrl+C to quit)');
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($input->getOption('watch')) {
            if (!$output instanceof ConsoleOutputInterface) {
                $output->writeln('<error>--watch requires an interactive terminal.</error>');

                return Command::FAILURE;
            }

            $section = $output->section();
            /* @phpstan-ignore while.alwaysTrue */
            while (true) {
                $section->clear();
                $section->writeln(\sprintf('<comment>Last updated: %s</comment>  Ctrl+C to quit', date('H:i:s')));
                $this->renderTable($input, $section);
                sleep(4);
            }
        }

        $this->renderTable($input, $output);

        return Command::SUCCESS;
    }

    private function renderTable(InputInterface $input, OutputInterface $output): void
    {
        $rows = $this->fetchRows($input);

        $table = new Table($output);
        $table->setHeaders(['ID', 'Domain Type', 'Status', 'Attempts', 'Occurred At', 'Next Retry At']);

        foreach ($rows as $row) {
            /** @var array{id: string, domain_type: string, status: string, attempt: string, occurred_at: string, next_retry_at: string|null} $row */
            $table->addRow([
                $row['id'],
                $row['domain_type'],
                $this->formatStatus($row['status']),
                $row['attempt'],
                $row['occurred_at'],
                $row['next_retry_at'] ?? '-',
            ]);
        }

        $table->render();
    }

    /** @return list<array<string, mixed>> */
    private function fetchRows(InputInterface $input): array
    {
        /** @var int|string|null $limit */
        $limit = $input->getOption('limit');
        $qb = $this->connection->createQueryBuilder()
            ->select('id', 'domain_type', 'status', 'attempt', 'occurred_at', 'next_retry_at')
            ->from('outbox_events')
            ->orderBy('occurred_at', 'DESC')
            ->setMaxResults((int) $limit);

        $status = $input->getOption('status');
        if (null !== $status) {
            $qb->andWhere('status = :status')->setParameter('status', $status);
        }

        $type = $input->getOption('type');
        if (null !== $type) {
            $qb->andWhere('domain_type = :type')->setParameter('type', $type);
        }

        return array_values($qb->fetchAllAssociative());
    }

    private function formatStatus(string $status): string
    {
        return match ($status) {
            'pending' => "<comment>$status</comment>",
            'mapped' => "<comment>$status</comment>",
            'sent' => "<info>$status</info>",
            'failed' => "<error>$status</error>",
            default => $status,
        };
    }
}
