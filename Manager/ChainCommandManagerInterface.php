<?php

namespace Timofiy\ChainCommandBundle\Manager;

/**
 * Interface  ChainCommandManagerInterface
 *
 * @author Timofiy Prisyazhnyuk <timofiyprisyazhnyuk@gmail.com>
 * @version 1.0
 */
interface ChainCommandManagerInterface
{
    /**
     * Method putCommandToChain
     *
     * @param string $rootCommand
     * @param string $memberCommand
     * @param array $arguments
     * @param int $sortIndex
     *
     * @return void
     */
    public function putCommandToChain(
        string $rootCommand,
        string $memberCommand,
        array $arguments = [],
        int $sortIndex = 15,
    ): void;

    /**
     * Method isRootCommand
     *
     * @param string $commandName
     *
     * @return bool
     */
    public function isRootCommand(string $commandName): bool;

    /**
     * Method getMembersForRoot
     *
     * @param string $rootCommand
     *
     * @return array
     */
    public function getMembersForRoot(string $rootCommand): array;
}