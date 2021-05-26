<?php

namespace PhpSagas\Orchestrator\Command;

use PhpSagas\Contracts\SagaDataInterface;

/**
 * @internal
 * Used as first local compensation (i.e. compensate actions done before saga started).
 *
 * @author Oleg Filatov <phpsagas@gmail.com>
 */
class NullCommand implements LocalCommandInterface
{
    public function execute(SagaDataInterface $sagaData): void
    {
        // nop
    }

    public function getSagaDataType(): string
    {
        return '';
    }
}
