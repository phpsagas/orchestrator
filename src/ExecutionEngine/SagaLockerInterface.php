<?php

namespace PhpSagas\Orchestrator\ExecutionEngine;

use PhpSagas\Orchestrator\InstantiationEngine\SagaInstanceInterface;

/**
 * Allows to lock saga.
 *
 * @author Oleg Filatov <phpsagas@gmail.com>
 */
interface SagaLockerInterface
{
    /**
     * @param SagaInstanceInterface $sagaInstance
     *
     * @return void
     * @throws SagaLockFailedException
     */
    public function lock(SagaInstanceInterface $sagaInstance): void;

    /**
     * @param SagaInstanceInterface $sagaInstance
     *
     * @return void
     */
    public function unlock(SagaInstanceInterface $sagaInstance): void;
}
