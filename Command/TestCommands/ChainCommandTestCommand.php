<?php

namespace App\Bundle\ChainCommandBundle\Command\TestCommands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ChainCommandTestCommand
 *
 * @author Timofiy Prisyazhnyuk <timofiyprisyazhnyuk@gmail.com>
 * @version 1.0
 */
class ChainCommandTestCommand extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'chain-command-test:hello';

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->write('Test Hello!');

        return Command::SUCCESS;
    }
}