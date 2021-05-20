<?php

namespace PhpSagas\Orchestrator\ExecutionEngine;

use PhpSagas\Orchestrator\InstantiationEngine\SagaInstanceInterface;

/**
 * Saga instance repository.
 *
 * @author Oleg Filatov <phpsagas@gmail.com>
 */
interface SagaInstanceRepositoryInterface
{
    /**
     * @param SagaInstanceInterface $sagaInstance
     *
     * @return string Saga ID
     */
    public function saveSaga(SagaInstanceInterface $sagaInstance): string;

    /**
     * @param string $sagaId
     *
     * @return SagaInstanceInterface
     */
    public function findSaga(string $sagaId): SagaInstanceInterface;
}
