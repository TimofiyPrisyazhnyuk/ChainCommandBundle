<?php

namespace App\Bundle\ChainCommandBundle\Manager;

use App\Bundle\ChainCommandBundle\Exception\InvalidChainCommandMemberException;

/**
 * Class ChainCommandManager
 * Provide a management for console command chains
 *
 * @author Timofiy Prisyazhnyuk <timofiyprisyazhnyuk@gmail.com>
 * @version 1.0
 */
class ChainCommandManager
{
    /**
     * @const string
     */
    public const SORT_INDEX_OPTION = 'sortIndex';
    public const ARGUMENTS_OPTION = 'arguments';
    public const MEMBER_COMMAND = 'memberCommand';

    /**
     * [
     *   "root:command" => [
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
     *   ...
     * ]
     *
     *
     * @var array
     */
    protected array $chainCommands = [];

    /**
     * @var bool
     */
    protected bool $detailedLoggingEnabled = true;

    /**
     * Put new command to chain
     *
     *  $arguments: [
     *    "key1" => (string)"value1",
     *    "key2" => (string)"value2"
     * ]
     *
     * @param string $rootCommand
     * @param string $memberCommand
     * @param array $arguments
     * @param int $sortIndex
     *
     * @return void
     * @throws InvalidChainCommandMemberException
     */
    public function putCommandToChain(
        string $rootCommand,
        string $memberCommand,
        array $arguments = [],
        int $sortIndex = 15,
    ): void {
        $this->checkIsAvailablePutToChain($rootCommand, $memberCommand);
        $this->chainCommands[$rootCommand][] = [
            static::MEMBER_COMMAND => $memberCommand,
            static::ARGUMENTS_OPTION => $arguments,
            static::SORT_INDEX_OPTION => $sortIndex
        ];
    }

    /**
     * Method setIsLoggingEnabled
     *
     * @param bool $isEnabled
     */
    public function setIsLoggingEnabled(bool $isEnabled): void
    {
        $this->detailedLoggingEnabled = $isEnabled;
    }

    /**
     * Method getIsDetailedLoggingEnabled
     *
     * @return bool
     */
    public function getIsDetailedLoggingEnabled(): bool
    {
        return $this->detailedLoggingEnabled;
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
     * Get root command for member
     *
     * @param string $memberCommand
     *
     * @return string
     */
    public function getRootForMember(string $memberCommand): string
    {
        foreach ($this->chainCommands as $rootCommand => $member) {
            $memberCommandList = array_column($member, static::MEMBER_COMMAND);
            if (in_array($memberCommand, $memberCommandList, true)) {
                return $rootCommand;
            }
        }
        return '';
    }

    /**
     * Get members commands list for root command
     *
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
     * @param string $rootCommand
     *
     * @return array
     */
    public function getMembersForRoot(string $rootCommand): array
    {
        if (array_key_exists($rootCommand, $this->chainCommands)) {
            $memberList = $this->chainCommands[$rootCommand];
            usort($memberList, function (array $first, array $second) {
                return ($first[static::SORT_INDEX_OPTION] - $second[static::SORT_INDEX_OPTION]);
            });
            return $memberList;
        }

        return [];
    }

    /**
     * Method checks is available member to put to command chain
     *
     * @param string $rootCommand
     * @param string $memberCommand
     *
     * @return void
     * @throws InvalidChainCommandMemberException
     */
    private function checkIsAvailablePutToChain(string $rootCommand, string $memberCommand): void
    {
        if (empty($this->chainCommands)) {
            return;
        }
        if ($this->isRootCommand($memberCommand)) {
            throw  new InvalidChainCommandMemberException($memberCommand);
        }
        if (!empty($this->getRootForMember($rootCommand))) {
            throw  new InvalidChainCommandMemberException($rootCommand);
        }
    }
}