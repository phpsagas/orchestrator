<?php

namespace PhpSagas\Orchestrator\ExecutionEngine;

use PhpSagas\Common\Message\CommandMessage;

/**
 * Sends the command message over concrete transport defined on implementation classes.
 *
 * @author Oleg Filatov <phpsagas@gmail.com>
 */
interface MessageProducerInterface
{
    /**
     * @param CommandMessage $message
     */
    public function send(CommandMessage $message): void;
}
