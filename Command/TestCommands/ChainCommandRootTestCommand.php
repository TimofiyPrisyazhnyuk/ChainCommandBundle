<?php

namespace App\Bundle\ChainCommandBundle\Command\TestCommands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ChainCommandRootTestCommand
 *
 * @author Timofiy Prisyazhnyuk <timofiyprisyazhnyuk@gmail.com>
 * @version 1.0
 */
class ChainCommandRootTestCommand extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'chain-command-root-test:hi';

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->write('Root test Hi!');

        return Command::SUCCESS;
    }
}