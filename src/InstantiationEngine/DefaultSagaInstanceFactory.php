<?php

namespace PhpSagas\Orchestrator\InstantiationEngine;

use PhpSagas\Orchestrator\BuildEngine\SagaDataInterface;
use PhpSagas\Orchestrator\BuildEngine\SagaInterface;
use PhpSagas\Orchestrator\ExecutionEngine\SagaSerializerInterface;

/**
 * @author Oleg Filatov <phpsagas@gmail.com>
 */
class DefaultSagaInstanceFactory implements SagaInstanceFactoryInterface
{
    /** @var SagaSerializerInterface */
    private $sagaSerializer;

    public function __construct(SagaSerializerInterface $sagaSerializer)
    {
        $this->sagaSerializer = $sagaSerializer;
    }

    public function makeSagaInstance(SagaInterface $saga, SagaDataInterface $sagaData): SagaInstanceInterface
    {
        $initialData = $saga->getSagaInitialData();

        return new DefaultSagaInstance(
            $saga->getSagaType(),
            $this->sagaSerializer->serialize($sagaData),
            get_class($sagaData),
            isset($initialData) ? $this->sagaSerializer->serialize($initialData) : null,
            isset($initialData) ? get_class($initialData) : null
        );
    }
}
