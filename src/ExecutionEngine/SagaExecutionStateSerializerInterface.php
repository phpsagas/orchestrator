<?php

namespace PhpSagas\Orchestrator\ExecutionEngine;

use PhpSagas\Orchestrator\BuildEngine\SagaExecutionState;

/**
 * Create saga execution state from serialized string.
 *
 * @author Oleg Filatov <phpsagas@gmail.com>
 */
interface SagaExecutionStateSerializerInterface
{
    /**
     * @param string $serializedState
     *
     * @return SagaExecutionState
     */
    public function deserialize(string $serializedState): SagaExecutionState;
}
