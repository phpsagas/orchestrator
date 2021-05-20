<?php

namespace PhpSagas\Orchestrator\InstantiationEngine;

/**
 * Represents saga instance concrete state.
 *
 * @author Oleg Filatov <phpsagas@gmail.com>
 */
interface SagaInstanceInterface
{
    /**
     * @return string|null
     */
    public function getSagaId(): ?string;

    /**
     * @param string $sagaId
     *
     * @return SagaInstanceInterface
     */
    public function setSagaId(string $sagaId): SagaInstanceInterface;

    /**
     * @return string
     */
    public function getSagaType(): string;

    /**
     * @return string
     */
    public function getStatus(): string;

    /**
     * @param string $status
     *
     * @return SagaInstanceInterface
     */
    public function setStatus(string $status): SagaInstanceInterface;

    /**
     * @return string|null
     */
    public function getLastMessageId(): ?string;

    /**
     * @param string $lastMessageId
     *
     * @return SagaInstanceInterface
     */
    public function setLastMessageId(string $lastMessageId): SagaInstanceInterface;

    /**
     * @return string
     */
    public function getSagaData(): string;

    /**
     * @return string
     */
    public function getSagaDataType(): string;

    /**
     * @param string $serializedSagaData
     * @param string $type
     *
     * @return SagaInstanceInterface
     */
    public function setSagaData(string $serializedSagaData, string $type): SagaInstanceInterface;

    /**
     * @return string|null
     */
    public function getInitialData(): ?string;

    /**
     * @return string|null
     */
    public function getInitialDataType(): ?string;

    /**
     * @return string
     */
    public function getExecutionState(): string;

    /**
     * @param string $serializedExecutionState
     *
     * @return SagaInstanceInterface
     */
    public function setExecutionState(string $serializedExecutionState): SagaInstanceInterface;
}
