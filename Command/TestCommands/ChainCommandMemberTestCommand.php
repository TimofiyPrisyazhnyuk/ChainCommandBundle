<?php

namespace App\Bundle\ChainCommandBundle\Command\TestCommands;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ChainCommandMemberTestCommand
 *
 * @author Timofiy Prisyazhnyuk <timofiyprisyazhnyuk@gmail.com>
 * @version 1.0
 */
class ChainCommandMemberTestCommand extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'chain-command-member-test:by';

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->write('Member test By!');

        return Command::SUCCESS;
    }
}