<?php

namespace App\Bundle\ChainCommandBundle\Tests\Functional\ChainCommand;

use App\Bundle\ChainCommandBundle\Command\TestCommands\ChainCommandMemberTestCommand;
use App\Bundle\ChainCommandBundle\Command\TestCommands\ChainCommandRootTestCommand;
use App\Bundle\ChainCommandBundle\Command\TestCommands\ChainCommandTestCommand;
use App\Bundle\ChainCommandBundle\Exception\InvalidChainCommandMemberException;
use App\Bundle\ChainCommandBundle\Manager\ChainCommandManager;
use App\Bundle\ChainCommandBundle\Manager\ChainCommandManagerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ChainCommandTest
 *
 * @author Timofiy Prisyazhnyuk <timofiyprisyazhnyuk@gmail.com>
 * @version 1.0
 */
class ChainCommandTest extends KernelTestCase
{
    /**
     * @var Application
     */
    protected Application $application;

    /**
     * @var OutputInterface
     */
    protected OutputInterface $bufferedOutput;

    /**
     * @var ChainCommandRootTestCommand
     */
    protected ChainCommandRootTestCommand $rootTestCommand;

    /**
     * @var ChainCommandMemberTestCommand
     */
    protected ChainCommandMemberTestCommand $memberTestCommand;

    /**
     * @var ChainCommandTestCommand
     */
    protected ChainCommandTestCommand $testCommand;

    /**
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $kernel = self::bootKernel();
        $this->application = new Application($kernel);
        $this->application->setAutoExit(false);

        $this->bufferedOutput = new BufferedOutput();
        $this->rootTestCommand = new ChainCommandRootTestCommand();
        $this->memberTestCommand = new ChainCommandMemberTestCommand();
        $this->testCommand = new ChainCommandTestCommand();
        // Add commands to Application
        $this->application->add($this->rootTestCommand);
        $this->application->add($this->memberTestCommand);
        $this->application->add($this->testCommand);
    }

    /**
     * Method testChainCommandsCallSuccess
     *
     * @covers ChainCommandManager::putCommandToChain
     *
     * @return void
     */
    public function testChainCommandsCallSuccess(): void
    {
        $container = static::getContainer();
        /** @var ChainCommandManagerInterface $manager */
        $manager = $container->get('chain_command.manager');
        $manager->putCommandToChain($this->rootTestCommand->getName(), $this->memberTestCommand->getName());
        $manager->putCommandToChain($this->rootTestCommand->getName(), $this->testCommand->getName());

        $resultCode = $this->application->run(
            new ArrayInput([$this->rootTestCommand->getName()]),
            $this->bufferedOutput
        );
        self::assertSame(Command::SUCCESS, $resultCode);
        self::assertEquals('Root test Hi!Member test By!Test Hello!', $this->bufferedOutput->fetch());
    }

    /**
     * Method testChainCommandsCallSuccess
     *
     * @covers ChainCommandManager::putCommandToChain
     *
     * @return void
     */
    public function testChainCommandsCallFailed(): void
    {
        $container = static::getContainer();

        $this->expectException(InvalidChainCommandMemberException::class);
        $this->expectExceptionMessage(
            'Is a member of ' . $this->memberTestCommand->getName() . ' command chain and cannot be executed on its own'
        );
        /** @var ChainCommandManagerInterface $manager */
        $manager = $container->get('chain_command.manager');
        $manager->putCommandToChain($this->rootTestCommand->getName(), $this->memberTestCommand->getName());
        // Try to set member command to root
        $manager->putCommandToChain($this->memberTestCommand->getName(), $this->testCommand->getName());
    }
}