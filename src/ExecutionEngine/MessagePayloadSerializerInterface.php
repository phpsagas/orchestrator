<?php

namespace PhpSagas\Orchestrator\ExecutionEngine;

use PhpSagas\Orchestrator\Command\CommandDataInterface;

/**
 * Provides command data serialization method.
 *
 * @author Oleg Filatov <phpsagas@gmail.com>
 */
interface MessagePayloadSerializerInterface
{
    /**
     * @param CommandDataInterface $commandData
     *
     * @return string
     */
    public function serialize(CommandDataInterface $commandData): string;
}
