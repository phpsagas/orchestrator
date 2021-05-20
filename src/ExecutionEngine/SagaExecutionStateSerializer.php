<?php

namespace PhpSagas\Orchestrator\ExecutionEngine;

use PhpSagas\Orchestrator\BuildEngine\SagaExecutionState;

/**
 * @author Oleg Filatov <phpsagas@gmail.com>
 */
class SagaExecutionStateSerializer implements SagaExecutionStateSerializerInterface
{
    /**
     * @param string $serializedState
     *
     * @return SagaExecutionState
     */
    public function deserialize(string $serializedState): SagaExecutionState
    {
        return SagaExecutionState::fromJson($serializedState);
    }
}
