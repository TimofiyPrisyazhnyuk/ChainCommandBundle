<?php

namespace App\Bundle\ChainCommandBundle\Tests\Functional\Manager;

use App\Bundle\ChainCommandBundle\Exception\InvalidChainCommandMemberException;
use App\Bundle\ChainCommandBundle\Manager\ChainCommandManager;
use PHPUnit\Framework\TestCase;

/**
 * Class ChainCommandManagerTest
 *
 * @author Timofiy Prisyazhnyuk <timofiyprisyazhnyuk@gmail.com>
 * @version 1.0
 */
class ChainCommandManagerTest extends TestCase
{
    /**
     * @var ChainCommandManager
     */
    private ChainCommandManager $chainCommandManager;

    /**
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->chainCommandManager = new ChainCommandManager();
    }

    /**
     * Method testPutCommandToChainFailed
     *
     * @covers ChainCommandManager::putCommandToChain
     * @covers ChainCommandManager::checkIsAvailablePutToChain
     *
     * @return void
     */
    public function testPutCommandToChainMemberFailed(): void
    {
        $this->expectException(InvalidChainCommandMemberException::class);
        $this->expectExceptionMessage('Is a member of member:command command chain and cannot be executed on its own');

        $this->chainCommandManager->putCommandToChain('root:command', 'member:command', []);
        $this->chainCommandManager->putCommandToChain('member:command', 'some:command', []);
    }


    /**
     * Method testPutCommandToChainFailed
     *
     * @covers ChainCommandManager::putCommandToChain
     * @covers ChainCommandManager::getRawChainCommands
     *
     * @return void
     */
    public function testPutCommandToChainStructureSuccess(): void
    {
        $this->chainCommandManager->putCommandToChain('root:command', 'member:command', [], 10);
        $this->chainCommandManager->putCommandToChain('newRoot:command', 'some:command', [], 15);

        $expectedStructure = [
            'root:command' => [
                [
                    'memberCommand' => 'member:command',
                    'arguments' => [],
                    'sortIndex' => 10
                ]
            ],
            'newRoot:command' => [
                [
                    'memberCommand' => 'some:command',
                    'arguments' => [],
                    'sortIndex' => 15
                ]
            ]
        ];
        $this->assertSame($expectedStructure, $this->chainCommandManager->getRawChainCommands());
    }

    /**
     * Method testPutCommandToChainRootIsMemberFailed
     *
     * @covers ChainCommandManager::putCommandToChain
     *
     * @return void
     */
    public function testPutCommandToChainRootIsMemberFailed(): void
    {
        $this->expectException(InvalidChainCommandMemberException::class);
        $this->expectExceptionMessage('Is a member of root:command command chain and cannot be executed on its own');

        $this->chainCommandManager->putCommandToChain('root:command', 'member:command', []);
        $this->chainCommandManager->putCommandToChain('new:command', 'root:command', []);
    }

    /**
     * Method testIsRootCommand
     *
     * @covers ChainCommandManager::isRootCommand
     *
     * @return void
     */
    public function testIsRootCommand(): void
    {
        $this->chainCommandManager->putCommandToChain('root:command', 'member:command', []);
        $this->chainCommandManager->putCommandToChain('root:command', 'some:command', []);

        self::assertTrue($this->chainCommandManager->isRootCommand('root:command'));
        self::assertFalse($this->chainCommandManager->isRootCommand('undefined:command'));
    }


    /**
     * Method testGetMembersForRootWithEmptyChain
     *
     * @covers ChainCommandManager::getMembersForRoot
     *
     * @return void
     */
    public function testGetMembersForRootWithEmptyChain(): void
    {
        self::assertSame([], $this->chainCommandManager->getMembersForRoot('root:command'));
    }

    /**
     * Method testGetMembersForRootWithSorting
     *
     * @covers ChainCommandManager::getMembersForRoot
     *
     * @return void
     */
    public function testGetMembersForRootWithSorting(): void
    {
        $this->chainCommandManager->putCommandToChain('root:command', 'member:command', ['key1' => 'value1'], 20);
        $this->chainCommandManager->putCommandToChain('root:command', 'some:command', ['key2' => 'value2'], 10);

        $membersStructure = [
            [
                'memberCommand' => 'some:command',
                'arguments' => [
                    'key2' => 'value2'
                ],
                'sortIndex' => 10
            ],
            [
                'memberCommand' => 'member:command',
                'arguments' => [
                    'key1' => 'value1'
                ],
                'sortIndex' => 20
            ]
        ];
        self::assertSame($membersStructure, $this->chainCommandManager->getMembersForRoot('root:command'));
    }

    /**
     * Method testGetFirstRootForMemberCommand
     *
     * @covers ChainCommandManager::getFirstRootForMember
     *
     * @return void
     */
    public function testGetFirstRootForMemberCommand(): void
    {
        $this->chainCommandManager->putCommandToChain('root:command', 'member:command', []);
        $this->chainCommandManager->putCommandToChain('root:command', 'some:command', []);
        $this->chainCommandManager->putCommandToChain('newRoot:command', 'new:command', []);

        self::assertSame('root:command', $this->chainCommandManager->getFirstRootForMember('some:command'));
        self::assertSame('root:command', $this->chainCommandManager->getFirstRootForMember('member:command'));
        self::assertSame('newRoot:command', $this->chainCommandManager->getFirstRootForMember('new:command'));
    }
}