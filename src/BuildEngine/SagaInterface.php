<?php

namespace PhpSagas\Orchestrator\BuildEngine;

use PhpSagas\Contracts\SagaDataInterface;

/**
 * @author Oleg Filatov <phpsagas@gmail.com>
 */
interface SagaInterface
{
    /**
     * @return SagaDefinition
     */
    public function getSagaDefinition(): SagaDefinition;

    /**
     * @return string
     */
    public function getSagaType(): string;

    /**
     * @return SagaDataInterface|null
     */
    public function getSagaInitialData(): ?SagaDataInterface;

    /**
     * Some logic trigger on saga starting.
     *
     * @param string            $sagaId
     * @param SagaDataInterface $data
     *
     * @return void
     */
    public function onStarted(string $sagaId, SagaDataInterface $data): void;

    /**
     * Some logic triggered if saga successfully finished.
     *
     * @param string            $sagaId
     * @param SagaDataInterface $data
     *
     * @return void
     */
    public function onFinished(string $sagaId, SagaDataInterface $data): void;

    /**
     * Some logic trigger if saga rolled back.
     *
     * @param string            $sagaId
     * @param SagaDataInterface $data
     *
     * @return void
     */
    public function onFailed(string $sagaId, SagaDataInterface $data): void;
}
