<?php

namespace App\Bundle\ChainCommandBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration
 *
 * @author Timofiy Prisyazhnyuk <timofiyprisyazhnyuk@gmail.com>
 * @version 1.0
 */
class Configuration implements ConfigurationInterface
{
    /**
     * @const string
     */
    public const ARGUMENTS_OPTION = 'arguments';
    public const SORT_INDEX_OPTION = 'sort_index';
    public const CHAIN_COMMANDS = 'chain_commands';
    public const DETAILED_LOGGING = 'detailed_logging';
    public const ENABLED = 'enabled';

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('timofiy_chain_command');
        $treeBuilder->getRootNode()
            ->children()
            ->arrayNode(static::DETAILED_LOGGING)
                ->canBeDisabled()
            ->end()
            ->arrayNode(static::CHAIN_COMMANDS)
                ->normalizeKeys(false)
                ->prototype('array')
                    ->normalizeKeys(false)
                    ->prototype('array')
                        ->normalizeKeys(false)
                        ->prototype('array')
                            ->normalizeKeys(false)
                            ->children()
                                ->arrayNode(static::ARGUMENTS_OPTION)
                                    ->normalizeKeys(false)
                                    ->prototype('scalar')
                                ->end()
                            ->end()
                            ->integerNode(static::SORT_INDEX_OPTION)
                            ->defaultValue(15)
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}