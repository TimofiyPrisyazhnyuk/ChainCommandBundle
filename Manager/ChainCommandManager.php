<?php

namespace App\Bundle\ChainCommandBundle\Manager;

/**
 * Class ChainCommandManager
 *
 * @author Timofiy Prisyazhnyuk <timofiyprisyazhnyuk@gmail.com>
 * @version 1.0
 */
class ChainCommandManager
{
    /**
     * @var array
     */
    protected array $chainCommands = [];

    /**
     * Put new command to chain
     *
     * @param string $rootCommand
     * @param string $memberCommand
     * @param array $arguments
     *
     * @return void
     */
    public function putCommandToChain(
        string $rootCommand,
        string $memberCommand,
        array $arguments = [],
    ): void
    {
        $this->chainCommands[$rootCommand][$memberCommand] = $arguments;
    }

    /**
     * Check if command is root
     *
     * @param string $commandName Current command name
     *
     * @return bool
     */
    public function isRootCommand(string $commandName): bool
    {
        return array_key_exists($commandName, $this->chainCommands);
    }

    /**
     * Check if is member command
     *
     * @param string $commandName
     *
     * @return bool
     */
    public function isMemberCommand(string $commandName): bool
    {
        foreach ($this->chainCommands as $chain) {
            if (array_key_exists($commandName, $chain)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get members commands list for root command
     *
     * @param string $rootCommand
     *
     * @return array
     */
    public function getMembersForRoot(string $rootCommand): array
    {
        if (array_key_exists($rootCommand, $this->chainCommands)) {
            return $this->chainCommands[$rootCommand];
        }

        return [];
    }
}