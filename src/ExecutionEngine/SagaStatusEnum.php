<?php

namespace PhpSagas\Orchestrator\ExecutionEngine;

/**
 * Enum representing the available saga statuses.
 *
 * @author Oleg Filatov <phpsagas@gmail.com>
 */
interface SagaStatusEnum
{
    /** @var string */
    public const STARTED = 'started';
    /** @var string */
    public const RUNNING = 'running';
    /** @var string */
    public const FINISHED = 'finished';
    /** @var string */
    public const FAILED = 'failed';
}
