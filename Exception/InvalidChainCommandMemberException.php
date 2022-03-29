<?php

namespace Timofiy\ChainCommandBundle\Exception;

use Exception;

/**
 * Class InvalidChainCommandMemberException
 *
 * @author Timofiy Prisyazhnyuk <timofiyprisyazhnyuk@gmail.com>
 * @version 1.0
 */
class InvalidChainCommandMemberException extends Exception
{
    /**
     * {@inheritdoc}
     */
    public function __construct($message, $code = 0, Exception $previous = null)
    {
        parent::__construct(
            sprintf('Is a member of %s command chain and cannot be executed on its own', $message),
            $code,
            $previous
        );
    }
}