<?php

namespace PhpSagas\Orchestrator\ExecutionEngine;

use PhpSagas\Orchestrator\BuildEngine\SagaActions;
use PhpSagas\Contracts\SagaDataInterface;
use PhpSagas\Orchestrator\BuildEngine\SagaInterface;
use PhpSagas\Orchestrator\InstantiationEngine\SagaInstanceInterface;

/**
 * Central point of saga execution engine.
 * Send remote commands, executes local commands and finishes saga execution.
 *
 * @author Oleg Filatov <phpsagas@gmail.com>
 */
interface SagaActionsProcessorInterface
{
    /**
     * @param SagaInterface         $saga
     * @param SagaInstanceInterface $sagaInstance
     * @param SagaDataInterface     $sagaData
     * @param SagaActions           $actions
     */
    public function processActions(
        SagaInterface $saga,
        SagaInstanceInterface $sagaInstance,
        SagaDataInterface $sagaData,
        SagaActions $actions
    ): void;
}
