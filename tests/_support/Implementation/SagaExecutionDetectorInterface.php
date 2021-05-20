<?php

namespace PhpSagas\Orchestrator\Tests\_support\Implementation;

/**
 * @author Oleg Filatov <phpsagas@gmail.com>
 */
interface SagaExecutionDetectorInterface
{
    public function starting();

    public function finished();

    public function failed();
}
