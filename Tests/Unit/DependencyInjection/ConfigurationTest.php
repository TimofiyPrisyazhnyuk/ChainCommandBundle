<?php

namespace App\Bundle\ChainCommandBundle\Tests\Unit\DependencyInjection;

use App\Bundle\ChainCommandBundle\DependencyInjection\Configuration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;

/**
 * Class ConfigurationTest
 *
 * @author Timofiy Prisyazhnyuk <timofiyprisyazhnyuk@gmail.com>
 * @version 1.0
 */
class ConfigurationTest extends TestCase
{
    /**
     * Method testGetConfigTreeBuilder
     *
     * @covers Configuration::getConfigTreeBuilder
     *
     * @return void
     */
    public function testGetConfigTreeBuilder(): void
    {
        $configuration = new Configuration();
        $builder = $configuration->getConfigTreeBuilder();

        self::assertInstanceOf(TreeBuilder::class, $builder);
    }

    /**
     * Method testProcessConfiguration
     *
     * @covers       Configuration::getConfigTreeBuilder
     * @dataProvider processConfigurationDataProvider
     */
    public function testProcessConfiguration(array $configs, array $expected): void
    {
        $configuration = new Configuration();
        $processor = new Processor();
        self::assertSame($expected, $processor->processConfiguration($configuration, $configs));
    }

    /**
     * Data provider for test testProcessConfiguration
     *
     * @return array
     */
    public function processConfigurationDataProvider(): array
    {
        $correctlyConfigurationStructure = [
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
        ];

        return [
            'configuration is empty' => [
                'configs' => [[]],
                'expected' => [
                    'detailed_logging' => [
                        'enabled' => true
                    ],
                    'chain_commands' => [],
                ],
            ],
            'configuration with correct structure' => [
                'configs' => [
                    'timofiy_chain_command' => [
                        ...$correctlyConfigurationStructure
                    ],
                ],
                'expected' => [
                    ...$correctlyConfigurationStructure
                ],
            ],
            'configuration with empty command chains' => [
                'configs' => [
                    'timofiy_chain_command' => [
                        'detailed_logging' => [
                            'enabled' => false
                        ],
                        'chain_commands' => [],
                    ],
                ],
                'expected' => [
                    'detailed_logging' => [
                        'enabled' => false
                    ],
                    'chain_commands' => [],
                ],
            ],
        ];
    }
}