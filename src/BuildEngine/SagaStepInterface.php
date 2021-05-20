<?php

namespace PhpSagas\Orchestrator\BuildEngine;

/**
 * Represents saga definition step.
 *
 * @author Oleg Filatov <phpsagas@gmail.com>
 */
interface SagaStepInterface
{
    /**
     * @return bool
     */
    public function hasCompensation(): bool;

    /**
     * @param bool $isCompensating
     *
     * @return ReplyHandlerInterface|null
     */
    public function getReplyHandler(bool $isCompensating): ?ReplyHandlerInterface;

    /**
     * @param SagaDataInterface $sagaData
     * @param bool              $isCompensating
     * @param SagaActions       $actions
     *
     * @return SagaActions
     */
    public function execute(SagaDataInterface $sagaData, bool $isCompensating, SagaActions $actions): SagaActions;
}
