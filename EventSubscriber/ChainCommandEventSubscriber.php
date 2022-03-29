<?php

namespace Timofiy\ChainCommandBundle\EventSubscriber;

use Timofiy\ChainCommandBundle\Manager\ChainCommandManager;
use LogicException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class ChainCommandEventSubscriber
 *
 * @author Timofiy Prisyazhnyuk <timofiyprisyazhnyuk@gmail.com>
 * @version 1.0
 */
class ChainCommandEventSubscriber implements EventSubscriberInterface
{
    /**
     * ChainCommandEventSubscriber constructor
     *
     * @param LoggerInterface $logger
     * @param ChainCommandManager $chainCommandManager
     */
    public function __construct(
        protected ChainCommandManager $chainCommandManager,
        protected LoggerInterface $logger,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ConsoleEvents::COMMAND => ['beforeCommand', 10],
            ConsoleEvents::TERMINATE => ['afterCommand', 10]
        ];
    }

    /**
     * Before console command subscriber event
     *
     * @param ConsoleCommandEvent $event
     */
    public function beforeCommand(ConsoleCommandEvent $event): void
    {
        $command = $event->getCommand();
        $commandName = $command->getName();
        $rootCommand = $this->chainCommandManager->getFirstRootForMember($commandName);
        if (!empty($rootCommand)) {
            $event->getOutput()->writeln(sprintf(
                'Error: %s command is a member of %s command chain and cannot be executed on its own.',
                $commandName, $rootCommand
            ));
            $event->disableCommand();
            return;
        }
        if ($this->chainCommandManager->isRootCommand($commandName)) {
            $this->formatLog(
                '%s is a master command of a command chain that has registered member commands',
                [$commandName]
            );

            foreach ($this->chainCommandManager->getMembersForRoot($commandName) as $memberCommand) {
                $this->formatLog(
                    '%s registered as a member of %s command chain',
                    [$memberCommand[ChainCommandManager::MEMBER_COMMAND], $commandName]
                );
            }
            $this->formatLog('Executing %s command itself first:', [$commandName]);
        }
    }

    /**
     * After console command subscriber event
     *
     * @param ConsoleTerminateEvent $event
     */
    public function afterCommand(ConsoleTerminateEvent $event): void
    {
        $command = $event->getCommand();
        if (!$this->chainCommandManager->isRootCommand($command?->getName())) {
            return;
        }
        $this->formatLog('Executing %s chain members:', [$command->getName()]);
        // Run all member commands
        $this->executeMembersCommand($event, $command);
        $this->formatLog('Execution of %s chain completed.', [$command->getName()]);
    }

    /**
     * Method executeMembersCommand
     *
     * @param ConsoleTerminateEvent $event
     * @param Command $command
     *
     * @return void
     */
    protected function executeMembersCommand(ConsoleTerminateEvent $event, Command $command): void
    {
        $application = $command->getApplication();
        if (null === $application) {
            throw new LogicException('Failed to determine application for console command event');
        }
        foreach ($this->chainCommandManager->getMembersForRoot($command->getName()) as $memberCommand) {
            $bufferedOutput = $this->getBufferedOutput();
            $application->get($memberCommand[ChainCommandManager::MEMBER_COMMAND])
                ->run($this->getArrayInput($memberCommand), $bufferedOutput);
            $outputMessage = $bufferedOutput->fetch();
            $event->getOutput()->write($outputMessage);
            $this->logger->info($outputMessage);
        }
    }

    /**
     * Method formatLog
     *
     * @param string $message
     * @param array $arguments
     *
     * @return void
     */
    protected function formatLog(string $message, array $arguments): void
    {
        if (!$this->chainCommandManager->getIsDetailedLoggingEnabled()) {
            return;
        }
        $this->logger->info(sprintf($message, ...$arguments));
    }

    /**
     * Method getBufferedOutput
     *
     * @return OutputInterface
     */
    protected function getBufferedOutput(): OutputInterface
    {
        return new BufferedOutput();
    }

    /**
     * Method getBufferedOutput
     *
     * @param array $memberCommand
     *
     * @return InputInterface
     */
    protected function getArrayInput(array $memberCommand): InputInterface
    {
        return new ArrayInput($memberCommand[ChainCommandManager::ARGUMENTS_OPTION]);
    }
}