<?php

namespace Timofiy\ChainCommandBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * Class ChainCommandExtension
 *
 * @author Timofiy Prisyazhnyuk <timofiyprisyazhnyuk@gmail.com>
 * @version 1.0
 */
class ChainCommandExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $commandChainsConfig = $this->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');

        if (empty($commandChainsConfig[Configuration::CHAIN_COMMANDS])) {
            return;
        }
        $this->loadChainConfiguration($container, $commandChainsConfig);
    }

    /**
     * Method loadChainConfiguration
     *
     * @param ContainerBuilder $container
     * @param array $commandChains
     *
     * @return void
     */
    protected function loadChainConfiguration(ContainerBuilder $container, array $commandChains): void
    {
        $definition = $container->getDefinition('chain_command.manager');
        if (isset($commandChains[Configuration::DETAILED_LOGGING][Configuration::ENABLED])) {
            $definition->addMethodCall(
                'setIsLoggingEnabled',
                [(bool)$commandChains[Configuration::DETAILED_LOGGING][Configuration::ENABLED]]
            );
        }
        foreach ($commandChains[Configuration::CHAIN_COMMANDS] as $rootCommand => $memberCommands) {
            foreach ($memberCommands as $subCommand) {
                $memberCommandName = key($subCommand);
                $definition->addMethodCall('putCommandToChain', [
                    $rootCommand,
                    $memberCommandName,
                    $subCommand[$memberCommandName][Configuration::ARGUMENTS_OPTION],
                    (int)$subCommand[$memberCommandName][Configuration::SORT_INDEX_OPTION]
                ]);
            }
        }
    }
}