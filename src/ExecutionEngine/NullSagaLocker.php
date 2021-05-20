<?php

namespace PhpSagas\Orchestrator\ExecutionEngine;

use PhpSagas\Orchestrator\InstantiationEngine\SagaInstanceInterface;

/**
 * No lock implementation.
 *
 * @author Oleg Filatov <phpsagas@gmail.com>
 */
class NullSagaLocker implements SagaLockerInterface
{
    /**
     * @inheritDoc
     */
    public function lock(SagaInstanceInterface $sagaInstance): void
    {
        // nop
    }

    /**
     * @inheritDoc
     */
    public function unlock(SagaInstanceInterface $sagaInstance): void
    {
        // nop
    }
}
