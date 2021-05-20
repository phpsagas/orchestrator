<?php

namespace PhpSagas\Orchestrator\BuildEngine;

/**
 * @author Oleg Filatov <phpsagas@gmail.com>
 */
class SagaDefinitionBuilder
{
    /** @var SagaStepInterface[] */
    private $sagaSteps = [];

    /**
     * @param SagaStepInterface $sagaStep
     */
    public function addStep(SagaStepInterface $sagaStep): void
    {
        $this->sagaSteps[] = $sagaStep;
    }

    /**
     * @return SagaDefinition
     */
    public function build(): SagaDefinition
    {
        return new SagaDefinition($this->sagaSteps);
    }
}
