<?php

declare(strict_types=1);

namespace Outbox\DependencyInjection;

use Outbox\Adapter\DoctrineOutboxAdapter;
use Outbox\CLI\ListOutboxEventsCommand;
use Outbox\CLI\ProcessOutboxCommand;
use Outbox\CLI\ShowOutboxEventCommand;
use Outbox\OutboxNotifier;
use Outbox\OutboxRecorder;
use Outbox\PostgresConnection;
use Outbox\Publisher\EventPublisher;
use Outbox\Publisher\IntegrationEventResolverInterface;
use Outbox\Publisher\MessengerEventPublisher;
use Outbox\Publisher\NullEventPublisher;
use Outbox\Worker\OutboxWorker;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Reference;

final class OutboxExtension extends Extension implements PrependExtensionInterface
{
    #[\Override]
    public function prepend(ContainerBuilder $container): void
    {
        $container->prependExtensionConfig('doctrine_migrations', [
            'migrations_paths' => [
                'Outbox\\Migrations' => __DIR__.'/../../migrations',
            ],
        ]);
    }

    #[\Override]
    public function load(array $configs, ContainerBuilder $container): void
    {
        /** @phpstan-var array{resolver: string, transport: string|null} $config */
        $config = $this->processConfiguration(new Configuration(), $configs);

        $container->setAlias(IntegrationEventResolverInterface::class, $config['resolver'])
            ->setPublic(true);

        $container->register(PostgresConnection::class)
            ->setAutowired(true)
            ->setPublic(false);

        $container->register(DoctrineOutboxAdapter::class)
            ->setAutowired(true)
            ->setPublic(false);

        $container->setAlias(OutboxRecorder::class, DoctrineOutboxAdapter::class)
            ->setPublic(true);

        $container->setAlias(OutboxNotifier::class, DoctrineOutboxAdapter::class)
            ->setPublic(true);

        $container->register(ListOutboxEventsCommand::class)
            ->setAutowired(true)
            ->addTag('console.command')
            ->setPublic(false);

        $container->register(ShowOutboxEventCommand::class)
            ->setAutowired(true)
            ->addTag('console.command')
            ->setPublic(false);

        if (null !== $config['transport']) {
            $container->register(MessengerEventPublisher::class)
                ->addArgument(new Reference('messenger.transport.'.$config['transport']))
                ->setPublic(false);

            $container->setAlias(EventPublisher::class, MessengerEventPublisher::class)
                ->setPublic(true);
        } else {
            $container->register(NullEventPublisher::class)
                ->setPublic(false);

            $container->setAlias(EventPublisher::class, NullEventPublisher::class)
                ->setPublic(true);
        }

        $container->register(OutboxWorker::class)
            ->setAutowired(true)
            ->setPublic(false);

        $container->register(ProcessOutboxCommand::class)
            ->setAutowired(true)
            ->addTag('console.command')
            ->setPublic(false);
    }

    #[\Override]
    public function getAlias(): string
    {
        return 'outbox';
    }
}
