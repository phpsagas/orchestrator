<?php

namespace PhpSagas\Orchestrator\BuildEngine;

use PhpSagas\Contracts\ReplyMessageInterface;
use PhpSagas\Contracts\SagaDataInterface;

/**
 * Contains saga steps definition.
 * Provides step execution methods.
 *
 * @author Oleg Filatov <phpsagas@gmail.com>
 */
class SagaDefinition
{
    /** @var SagaStepInterface[] */
    private $sagaSteps;

    public function __construct(array $sagaSteps)
    {
        $this->sagaSteps = $sagaSteps;
    }

    /**
     * @param SagaDataInterface $sagaData
     *
     * @return SagaActions
     */
    public function start(SagaDataInterface $sagaData): SagaActions
    {
        return $this->executeNextStep($sagaData, SagaExecutionState::start());
    }

    /**
     * @param SagaExecutionState    $executionState
     * @param SagaDataInterface     $sagaData
     * @param ReplyMessageInterface $message
     *
     * @return SagaActions
     */
    public function handleReply(
        SagaExecutionState $executionState,
        SagaDataInterface $sagaData,
        ReplyMessageInterface $message
    ): SagaActions {
        $currentStep = $this->sagaSteps[$executionState->getCurrentStepIndex()];
        $isCompensating = $executionState->isCompensating();

        $handler = $currentStep->getReplyHandler($isCompensating);
        if (isset($handler)) {
            $handler->handle($message, $sagaData);
        }

        if ($message->isSuccess()) {
            return $this->executeNextStep($sagaData, $executionState);
        }

        if ($isCompensating) {
            throw new \LogicException('Failure when compensating the compensating transaction');
        }

        return $this->executeNextStep($sagaData, $executionState->startCompensating());
    }

    /**
     * @param SagaDataInterface  $sagaData
     * @param SagaExecutionState $state
     *
     * @return SagaActions
     */
    private function executeNextStep(SagaDataInterface $sagaData, SagaExecutionState $state): SagaActions
    {
        $executionStep = $this->nextExecutionStep($state);

        if ($executionStep->isEmpty()) {
            return $this->makeEndStateSagaActions($state);
        }

        return $executionStep->execute($sagaData, $state);
    }

    /**
     * @param SagaExecutionState $state
     *
     * @return ExecutionStep
     */
    private function nextExecutionStep(SagaExecutionState $state): ExecutionStep
    {
        $skipped = 0;
        $isCompensating = $state->isCompensating();
        $direction = ($isCompensating ? -1 : +1);
        $stepsCount = count($this->sagaSteps);

        for ($i = $state->getCurrentStepIndex() + $direction; $i >= 0 && $i < $stepsCount; $i += $direction) {
            $step = $this->sagaSteps[$i];
            if ($isCompensating ? $step->hasCompensation() : true) {
                return new ExecutionStep($step, $skipped, $isCompensating);
            }

            $skipped++;
        }

        return new ExecutionStep(null, $skipped, $isCompensating);
    }

    /**
     * @param SagaExecutionState $state
     *
     * @return SagaActions
     */
    private function makeEndStateSagaActions(SagaExecutionState $state): SagaActions
    {
        return new SagaActions(SagaExecutionState::finish()->toJson(), $state->isCompensating());
    }
}
