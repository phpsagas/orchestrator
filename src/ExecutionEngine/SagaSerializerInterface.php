<?php

namespace PhpSagas\Orchestrator\ExecutionEngine;

use PhpSagas\Orchestrator\BuildEngine\SagaDataInterface;

/**
 * Allows to serialize and deserialize saga data.
 *
 * @author Oleg Filatov <phpsagas@gmail.com>
 */
interface SagaSerializerInterface
{
    /**
     * @param SagaDataInterface $sagaData
     *
     * @return string
     */
    public function serialize(SagaDataInterface $sagaData): string;

    /**
     * @param string $sagaData
     *
     * @param string $type
     *
     * @return SagaDataInterface
     */
    public function deserialize(string $sagaData, string $type): SagaDataInterface;
}
