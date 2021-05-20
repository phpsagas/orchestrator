<?php

namespace PhpSagas\Orchestrator\Command;

use PhpSagas\Orchestrator\BuildEngine\SagaDataInterface;

/**
 * Should be implemented by classes prepare command messages to perform operations outside of current project.
 *
 * @author Oleg Filatov <phpsagas@gmail.com>
 */
interface RemoteCommandInterface
{
    /**
     * @return string
     */
    public function getCommandType(): string;

    /**
     * Returns a type expected self::getCommandData input params.
     *
     * @return string
     */
    public function getSagaDataClassName(): string;

    /**
     * Returns a data for remote command execution (data using another application services).
     *
     * @param SagaDataInterface $sagaData Type of data should be equal to self::getSagaDataClassName
     *
     * @return CommandDataInterface
     */
    public function getCommandData(SagaDataInterface $sagaData): CommandDataInterface;
}
