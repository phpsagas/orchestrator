<?php

namespace PhpSagas\Orchestrator\ExecutionEngine;

/**
 * Exception is thrown when saga lock can not be acquired.
 *
 * @author Oleg Filatov <phpsagas@gmail.com>
 */
class SagaLockFailedException extends \RuntimeException
{

}
