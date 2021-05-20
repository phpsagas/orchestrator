<?php

namespace PhpSagas\Orchestrator\Tests\_support\Implementation;

/**
 * @author Oleg Filatov <phpsagas@gmail.com>
 */
interface CommandExecutionDetectorInterface
{
    public function execute();
}
