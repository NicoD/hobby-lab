<?php

declare(strict_types=1);

namespace Outbox\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    #[\Override]
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('outbox');

        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('resolver')
                    ->isRequired()
                    ->cannotBeEmpty()
                    ->info('Service id implementing IntegrationEventResolverInterface.')
                ->end()
                ->scalarNode('transport')
                    ->defaultNull()
                    ->info('Name of the Symfony Messenger transport to publish events to. Required when the outbox worker is active.')
                ->end()
            ->end();

        return $treeBuilder;
    }
}
