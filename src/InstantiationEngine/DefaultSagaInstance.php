<?php

namespace PhpSagas\Orchestrator\InstantiationEngine;

use PhpSagas\Orchestrator\ExecutionEngine\SagaStatusEnum;

/**
 * @author Oleg Filatov <phpsagas@gmail.com>
 */
class DefaultSagaInstance implements SagaInstanceInterface
{
    /** @var string|null */
    protected $sagaId;
    /** @var string */
    protected $sagaType;
    /** @var string|null */
    protected $lastMessageId;
    /** @var string */
    protected $sagaData;
    /** @var string */
    protected $sagaDataType;
    /** @var string */
    protected $initialData;
    /** @var string */
    protected $initialDataType;
    /** @var string */
    protected $executionState;
    /** @var string */
    protected $status;

    public function __construct(
        string $sagaType,
        string $serializedSagaData,
        string $sagaDataType,
        ?string $serializedInitialData,
        ?string $initialDataType,
        string $executionState = '{}',
        string $status = SagaStatusEnum::STARTED
    ) {
        $this->sagaType = $sagaType;
        $this->sagaData = $serializedSagaData;
        $this->sagaDataType = $sagaDataType;
        $this->executionState = $executionState;
        $this->initialData = $serializedInitialData;
        $this->initialDataType = $initialDataType;
        $this->status = $status;
    }

    /**
     * @return string|null
     */
    public function getSagaId(): ?string
    {
        return $this->sagaId;
    }

    /**
     * @param string $sagaId
     *
     * @return SagaInstanceInterface
     */
    public function setSagaId(string $sagaId): SagaInstanceInterface
    {
        $this->sagaId = $sagaId;
        return $this;
    }

    /**
     * @return string
     */
    public function getSagaType(): string
    {
        return $this->sagaType;
    }

    /**
     * @return string|null
     */
    public function getLastMessageId(): ?string
    {
        return $this->lastMessageId;
    }

    /**
     * @param string $lastMessageId
     *
     * @return SagaInstanceInterface
     */
    public function setLastMessageId(string $lastMessageId): SagaInstanceInterface
    {
        $this->lastMessageId = $lastMessageId;
        return $this;
    }

    /**
     * @return string
     */
    public function getSagaData(): string
    {
        return $this->sagaData;
    }

    /**
     * @return string
     */
    public function getSagaDataType(): string
    {
        return $this->sagaDataType;
    }

    /**
     * @param string $sagaData
     * @param string $sagaDataType
     *
     * @return SagaInstanceInterface
     */
    public function setSagaData(string $sagaData, string $sagaDataType): SagaInstanceInterface
    {
        $this->sagaData = $sagaData;
        $this->sagaDataType = $sagaDataType;

        return $this;
    }

    /**
     * @return string
     */
    public function getExecutionState(): string
    {
        return $this->executionState;
    }

    /**
     * @param string $executionState
     *
     * @return SagaInstanceInterface
     */
    public function setExecutionState(string $executionState): SagaInstanceInterface
    {
        $this->executionState = $executionState;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getInitialData(): ?string
    {
        return $this->initialData;
    }

    /**
     * @return string|null
     */
    public function getInitialDataType(): ?string
    {
        return $this->initialDataType;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     *
     * @return SagaInstanceInterface
     */
    public function setStatus(string $status): SagaInstanceInterface
    {
        $this->status = $status;
        return $this;
    }
}
