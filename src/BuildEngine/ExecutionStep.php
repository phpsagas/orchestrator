<?php

namespace PhpSagas\Orchestrator\BuildEngine;

/**
 * Step of saga execution.
 *
 * @author Oleg Filatov <phpsagas@gmail.com>
 */
class ExecutionStep
{
    /** @var SagaStepInterface|null */
    private $step;
    /** @var int */
    private $skipped;
    /** @var bool */
    private $isCompensating;

    public function __construct(?SagaStepInterface $step, int $skipped, bool $isCompensating)
    {
        $this->step = $step;
        $this->skipped = $skipped;
        $this->isCompensating = $isCompensating;
    }

    /**
     * @param SagaDataInterface  $sagaData
     * @param SagaExecutionState $currentState
     *
     * @return SagaActions
     */
    public function execute(
        SagaDataInterface $sagaData,
        SagaExecutionState $currentState
    ): SagaActions {
        $newState = $currentState->nextState($this->stepIndex());

        $sagaActions = new SagaActions(
            $newState->toJson(),
            $currentState->isCompensating(),
            false,
            $sagaData
        );

        return $this->step->execute($sagaData, $this->isCompensating, $sagaActions);
    }

    /**
     * @return int
     */
    public function stepIndex(): int
    {
        return (isset($this->step) ? 1 : 0) + $this->skipped;
    }

    /**
     * @return bool
     */
    public function isEmpty(): bool
    {
        return is_null($this->step);
    }
}
