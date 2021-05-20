<?php

namespace PhpSagas\Orchestrator\BuildEngine;

/**
 * Represents current step execution state.
 *
 * @author Oleg Filatov <phpsagas@gmail.com>
 */
class SagaExecutionState
{
    /** @var int|null */
    private $currentStepIndex;
    /** @var bool */
    private $isCompensating;

    public static function fromJson(string $sagaStateJson): self
    {
        $state = \json_decode($sagaStateJson, JSON_OBJECT_AS_ARRAY);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new \InvalidArgumentException(json_last_error_msg());
        }

        return new self($state['currentStepIndex'], $state['isCompensating']);
    }

    public static function start(): self
    {
        return new self(-1);
    }

    public static function finish(): self
    {
        return new self(null, false);
    }

    public function startCompensating(): self
    {
        return new self($this->currentStepIndex, true);
    }

    private function __construct(?int $currentStepIndex, bool $compensating = false)
    {
        $this->currentStepIndex = $currentStepIndex;
        $this->isCompensating = $compensating;
    }

    public function toJson(): string
    {
        return \json_encode(
            [
                'currentStepIndex' => $this->currentStepIndex,
                'isCompensating' => $this->isCompensating,
            ]
        );
    }

    public function nextState(int $delta): SagaExecutionState
    {
        $nextStepIndex = ($this->isCompensating ? $this->currentStepIndex - $delta : $this->currentStepIndex + $delta);
        return new self($nextStepIndex, $this->isCompensating);
    }

    /**
     * @return int|null
     */
    public function getCurrentStepIndex(): ?int
    {
        return $this->currentStepIndex;
    }

    /**
     * @return bool
     */
    public function isCompensating(): bool
    {
        return $this->isCompensating;
    }
}
