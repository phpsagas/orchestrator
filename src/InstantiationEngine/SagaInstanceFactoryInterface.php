<?php

namespace PhpSagas\Orchestrator\InstantiationEngine;

use PhpSagas\Orchestrator\BuildEngine\SagaDataInterface;
use PhpSagas\Orchestrator\BuildEngine\SagaInterface;

/**
 * Factory for saga instance creation.
 *
 * @author Oleg Filatov <phpsagas@gmail.com>
 */
interface SagaInstanceFactoryInterface
{
    /**
     * @param SagaInterface     $saga
     * @param SagaDataInterface $sagaData
     *
     * @return SagaInstanceInterface
     */
    public function makeSagaInstance(SagaInterface $saga, SagaDataInterface $sagaData): SagaInstanceInterface;
}
