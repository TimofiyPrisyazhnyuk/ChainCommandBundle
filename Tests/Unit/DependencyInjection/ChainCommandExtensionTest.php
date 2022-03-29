<?php

namespace Timofiy\ChainCommandBundle\Tests\Unit\DependencyInjection;

use Timofiy\ChainCommandBundle\DependencyInjection\ChainCommandExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class ChainCommandExtensionTest
 *
 * @author Timofiy Prisyazhnyuk <timofiyprisyazhnyuk@gmail.com>
 * @version 1.0
 */
class ChainCommandExtensionTest extends TestCase
{
    /**
     * @var ChainCommandExtension
     */
    private ChainCommandExtension $extension;

    /**
     * @var ContainerBuilder
     */
    private ContainerBuilder $container;

    /**
     * Method setUp
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->container = new ContainerBuilder();
        $this->extension = new ChainCommandExtension();
    }

    /**
     * Method testLoadWithoutChainConfig
     *
     * @covers ChainCommandExtension::load
     *
     * @return void
     */
    public function testLoadWithoutChainConfig(): void
    {
        $this->extension->load([], $this->container);

        self::assertTrue($this->container->hasDefinition('chain_command.manager'));
    }

    /**
     * Method testLoadWithChainConfig
     *
     * @covers       ChainCommandExtension::load
     * @covers       ChainCommandExtension::loadChainConfiguration
     * @dataProvider LoadLoadWithChainConfigDataProvider
     *
     * @param array $config
     *
     * @return void
     */
    public function testLoadWithChainConfig(array $config): void
    {
        $this->extension->load($config, $this->container);
        self::assertTrue($this->container->hasDefinition('chain_command.manager'));
        $chainCommandManager = $this->container->getDefinition('chain_command.manager');
        self::assertTrue($chainCommandManager->hasMethodCall('setIsLoggingEnabled'));
        self::assertTrue($chainCommandManager->hasMethodCall('putCommandToChain'));
    }

    /**
     * Method LoadLoadWithChainConfigDataProvider
     *
     * @return array
     */
    public function LoadLoadWithChainConfigDataProvider(): array
    {
        return [
            'with correct configuration' => [
                'config' => [
                    'timofiy_chain_command' => [
                        'detailed_logging' => [
                            'enabled' => true
                        ],
                        'chain_commands' => [
                            'root:command' => [
                                [
                                    'bar:hi' => [
                                        'arguments' => ['argName' => 'argVal'],
                                        'sort_index' => 10
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }
}