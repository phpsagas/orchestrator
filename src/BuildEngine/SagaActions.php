<?php

namespace PhpSagas\Orchestrator\BuildEngine;

use PhpSagas\Orchestrator\Command\LocalCommandException;
use PhpSagas\Orchestrator\Command\RemoteCommandInterface;

/**
 * Represents saga step state.
 *
 * @author Oleg Filatov <phpsagas@gmail.com>
 */
class SagaActions
{
    /** @var RemoteCommandInterface|null */
    private $command;
    /** @var SagaDataInterface|null */
    private $updatedSagaData;
    /** @var string|null */
    private $executionState;
    /** @var bool */
    private $isEndState;
    /** @var bool */
    private $isCompensating;
    /** @var bool */
    private $isLocal = false;
    /** @var LocalCommandException|null */
    private $localException;

    public function __construct(
        ?string $executionState,
        bool $isCompensating,
        bool $isEndState = true,
        SagaDataInterface $updatedSagaData = null
    ) {
        $this->updatedSagaData = $updatedSagaData;
        $this->executionState = $executionState;
        $this->isEndState = $isEndState;
        $this->isCompensating = $isCompensating;
    }

    /**
     * @return RemoteCommandInterface
     */
    public function getCommand(): RemoteCommandInterface
    {
        if (is_null($this->command)) {
            throw new \LogicException('Remote command is empty');
        }

        return $this->command;
    }

    /**
     * @return SagaDataInterface|null
     */
    public function getUpdatedSagaData(): ?SagaDataInterface
    {
        return $this->updatedSagaData;
    }

    public function getUpdatedSagaDataOrFail(): SagaDataInterface
    {
        if (\is_null($this->updatedSagaData)) {
            throw new \RuntimeException('Updated saga data not found');
        }

        return $this->updatedSagaData;
    }

    /**
     * @return string|null
     */
    public function getExecutionState(): ?string
    {
        return $this->executionState;
    }

    public function getExecutionStateOrFail(): string
    {
        if (\is_null($this->executionState)) {
            throw new \RuntimeException('Execution state not found');
        }

        return $this->executionState;
    }

    /**
     * @return bool
     */
    public function isEndState(): bool
    {
        return $this->isEndState;
    }

    /**
     * @return bool
     */
    public function isCompensating(): bool
    {
        return $this->isCompensating;
    }

    /**
     * @return bool
     */
    public function isLocal(): bool
    {
        return $this->isLocal;
    }

    public function setLocal(LocalCommandException $e = null): self
    {
        $this->isLocal = true;
        $this->localException = $e;

        return $this;
    }

    public function setRemote(RemoteCommandInterface $command): self
    {
        $this->command = $command;
        return $this;
    }

    /**
     * @return LocalCommandException|null
     */
    public function getLocalException(): ?LocalCommandException
    {
        return $this->localException;
    }
}
