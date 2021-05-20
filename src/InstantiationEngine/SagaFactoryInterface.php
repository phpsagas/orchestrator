<?php

namespace PhpSagas\Orchestrator\InstantiationEngine;

use PhpSagas\Orchestrator\BuildEngine\SagaDataInterface;
use PhpSagas\Orchestrator\BuildEngine\SagaInterface;

/**
 * Factory for sagas creation.
 *
 * @author Oleg Filatov <phpsagas@gmail.com>
 */
interface SagaFactoryInterface
{
    /**
     * @param string                 $sagaType
     * @param SagaDataInterface|null $sagaInitialData
     *
     * @return SagaInterface
     */
    public function create(string $sagaType, SagaDataInterface $sagaInitialData = null): SagaInterface;
}
