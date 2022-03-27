<?php

namespace App\Bundle\ChainCommandBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Yaml\Yaml;

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
        $configs = Yaml::parse(file_get_contents(__DIR__ . '/../Resources/config/config.yml'));
        $configuration = new Configuration();
        die(var_dump($configs));
        $commandChainsConfig = $this->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');

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
        foreach ($commandChains as $rootCommand => $memberCommands) {
            foreach ($memberCommands as $subCommandName => $subCommandArgs) {
                $definition->addMethodCall('putCommandToChain', [
                    $rootCommand,
                    $subCommandName,
                    $subCommandArgs[Configuration::ARGUMENTS_OPTION]
                ]);
            }
        }
    }
}