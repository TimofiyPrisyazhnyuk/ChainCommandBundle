<?php

namespace App\Bundle\ChainCommandBundle\EventSubscriber;

use App\Bundle\ChainCommandBundle\Exception\InvalidChainCommandMemberException;
use App\Bundle\ChainCommandBundle\Manager\ChainCommandManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
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
     * CommandSubscriber constructor
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
     * @throws InvalidChainCommandMemberException
     */
    public function beforeCommand(ConsoleCommandEvent $event): void
    {
        $command = $event->getCommand();
        if ($this->chainCommandManager->isMemberCommand($command->getName())) {
            throw new InvalidChainCommandMemberException($command->getName());
        }

        if ($this->chainCommandManager->isRootCommand($command->getName())) {
            $this->logger->info(
                sprintf(
                    '%s is a master command of a command chain that has registered member commands',
                    $command->getName()
                )
            );
            foreach ($this->chainCommandManager->getMembersForRoot($command->getName()) as $memberCommand => $arguments) {
                $this->logger->info(
                    sprintf('%s registered as a member of %s command chain', $memberCommand, $command->getName())
                );
            }
            $this->logger->info(sprintf('Executing %s command itself first:', $command->getName()));
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
        if (!$this->chainCommandManager->isRootCommand($command->getName())) {
            return;
        }

        $this->logger->info(sprintf('Executing %s chain members:', $command->getName()));
        $application = $command->getApplication();

        foreach ($this->chainCommandManager->getMembersForRoot($command->getName()) as $memberCommand => $arguments) {
            $bufferedOutput = $this->getBufferedOutput();
            $application->get($memberCommand)->run($event->getInput(), $bufferedOutput);
            $outputMessage = $bufferedOutput->fetch();
            $event->getOutput()->writeln($outputMessage);
            $this->logger->info($outputMessage);
        }

        $this->logger->info(sprintf('Execution of %s chain completed.', $command->getName()));
    }

    /**
     * Method getBufferedOutput
     *
     * @return OutputInterface
     */
    private function getBufferedOutput(): OutputInterface
    {
        return new BufferedOutput();
    }
}