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

    /**
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('chain_commands');
        $treeBuilder->getRootNode()
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
                        ->end()
                    ->end();

        return $treeBuilder;
    }
}