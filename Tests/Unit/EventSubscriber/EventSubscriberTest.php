<?php

namespace App\Bundle\ChainCommandBundle\Tests\Unit\EventSubscriber;

use App\Bundle\ChainCommandBundle\EventSubscriber\ChainCommandEventSubscriber;
use App\Bundle\ChainCommandBundle\Manager\ChainCommandManager;
use LogicException;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class EventSubscriberTest
 *
 * @author Timofiy Prisyazhnyuk <timofiyprisyazhnyuk@gmail.com>
 * @version 1.0
 */
class EventSubscriberTest extends TestCase
{
    /**
     * Method testBeforeCommandIsMemberError
     *
     * @covers ChainCommandEventSubscriber::beforeCommand
     *
     * @return void
     */
    public function testBeforeCommandIsMemberError(): void
    {
        $chainManager = $this->createMock(ChainCommandManager::class);
        $chainManager->expects($this->once())
            ->method('getFirstRootForMember')
            ->with('test:command')
            ->willReturn('root:command');

        $consoleCommand = $this->createMock(Command::class);
        $consoleCommand->expects($this->once())
            ->method('getName')
            ->willReturn('test:command');

        $output = $this->createMock(OutputInterface::class);
        $output->expects($this->once())
            ->method('writeln')
            ->with(
                'Error: test:command command is a member of root:command command chain and cannot be executed on its own.'
            );

        $consoleCommandEvent = new ConsoleCommandEvent(
            $consoleCommand,
            $this->createMock(InputInterface::class),
            $output
        );

        $chainCommandEventSubscriber = new ChainCommandEventSubscriber(
            $chainManager,
            $this->createMock(LoggerInterface::class)
        );
        $chainCommandEventSubscriber->beforeCommand($consoleCommandEvent);
    }

    /**
     * Method testBeforeCommandRootCallSuccess
     *
     * @covers       ChainCommandEventSubscriber::beforeCommand
     * @dataProvider beforeCommandSuccessDataProvider
     *
     * @param string $rootCommand
     * @param array $memberCommandsList
     * @param array $expectedLogs
     *
     * @return void
     */
    public function testBeforeCommandRootCallSuccess(
        string $rootCommand,
        array $memberCommandsList,
        array $expectedLogs
    ): void {
        $chainManager = $this->createMock(ChainCommandManager::class);
        $chainManager->expects($this->once())
            ->method('isRootCommand')
            ->with($rootCommand)
            ->willReturn(true);
        $chainManager->expects($this->once())
            ->method('getFirstRootForMember')
            ->with($rootCommand)
            ->willReturn('');
        $chainManager->expects($this->once())
            ->method('getMembersForRoot')
            ->with($rootCommand)
            ->willReturn($memberCommandsList);
        $chainManager->expects($this->exactly(count($expectedLogs)))
            ->method('getIsDetailedLoggingEnabled')
            ->willReturn(true);

        $consoleCommand = $this->createMock(Command::class);
        $consoleCommand->expects($this->once())
            ->method('getName')
            ->willReturn($rootCommand);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->exactly(count($expectedLogs)))
            ->method('info')
            ->withConsecutive(...$expectedLogs);

        $consoleCommandEvent = new ConsoleCommandEvent(
            $consoleCommand,
            $this->createMock(InputInterface::class),
            $this->createMock(OutputInterface::class)
        );
        $chainCommandEventSubscriber = new ChainCommandEventSubscriber($chainManager, $logger);
        $chainCommandEventSubscriber->beforeCommand($consoleCommandEvent);
    }

    /**
     * Data provider for test testBeforeCommandSuccess
     *   [
     *      [
     *        "memberCommand" => (string)"member:command",
     *        "arguments" => [
     *           "key1" => (string)"value1",
     *           "key2" => (string)"value2"
     *         ],
     *         "sortIndex" => (int)14
     *      ]
     *      ...
     *   ]
     *
     * @return array
     *
     */
    public function beforeCommandSuccessDataProvider(): array
    {
        return [
            'with commands chain list' => [
                'rootCommand' => 'root:command',
                'memberCommandsList' => [
                    [
                        'memberCommand' => 'test:hello',
                        'arguments' => [],
                        'sortIndex' => 5
                    ],
                    [
                        'memberCommand' => 'test:hi',
                        'arguments' => [],
                        'sortIndex' => 10
                    ]
                ],
                'expectedLogs' => [
                    ['root:command is a master command of a command chain that has registered member commands'],
                    ['test:hello registered as a member of root:command command chain'],
                    ['test:hi registered as a member of root:command command chain'],
                    ['Executing root:command command itself first:']
                ]
            ],
            'with empty command chain list' => [
                'rootCommand' => 'root:command',
                'memberCommandsList' => [],
                'expectedLogs' => [
                    ['root:command is a master command of a command chain that has registered member commands'],
                    ['Executing root:command command itself first:']
                ]
            ]
        ];
    }


    /**
     * Method testAfterCommandApplicationDoesNotExistFailed
     *
     * @covers ChainCommandEventSubscriber::afterCommand
     *
     * @return void
     */
    public function testAfterCommandApplicationDoesNotExistFailed(): void
    {
        $chainManager = $this->createMock(ChainCommandManager::class);
        $chainManager->expects($this->once())
            ->method('isRootCommand')
            ->with('root:command')
            ->willReturn(true);

        $consoleCommand = $this->createMock(Command::class);
        $consoleCommand->expects($this->once())
            ->method('getApplication')
            ->willReturn(null);
        $consoleCommand->expects($this->exactly(2))
            ->method('getName')
            ->willReturn('root:command');

        $consoleTerminateEvent = new ConsoleTerminateEvent(
            $consoleCommand,
            $this->createMock(InputInterface::class),
            $this->createMock(OutputInterface::class),
            1
        );
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Failed to determine application for console command event');

        $chainCommandEventSubscriber = new ChainCommandEventSubscriber(
            $chainManager,
            $this->createMock(LoggerInterface::class)
        );
        $chainCommandEventSubscriber->afterCommand($consoleTerminateEvent);
    }


    /**
     * Method testAfterCommandEventSuccess
     *
     * @covers ChainCommandEventSubscriber::afterCommand
     *
     * @return void
     */
    public function testAfterCommandEventSuccess(): void
    {
        // TODO: need to implement test for ChainCommandEventSubscriber::afterCommand
        self::assertTrue(true);
    }
}