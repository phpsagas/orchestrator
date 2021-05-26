<?php

namespace PhpSagas\Orchestrator\ExecutionEngine;

use PhpSagas\Contracts\SagaDataInterface;
use PhpSagas\Orchestrator\Command\RemoteCommandInterface;

/**
 * Saga command producer interface.
 *
 * @author Oleg Filatov <phpsagas@gmail.com>
 */
interface SagaCommandProducerInterface
{
    /**
     * Send message for remote command execution.
     *
     * @param string                 $sagaId
     * @param string                 $sagaType
     * @param SagaDataInterface      $sagaData
     * @param RemoteCommandInterface $command
     * @param string                 $messageId
     *
     * @return void
     */
    public function send(
        string $sagaId,
        string $sagaType,
        SagaDataInterface $sagaData,
        RemoteCommandInterface $command,
        string $messageId
    ): void;
}
