<?php

namespace PhpSagas\Orchestrator\BuildEngine;

use PhpSagas\Contracts\ReplyMessageInterface;
use PhpSagas\Contracts\SagaDataInterface;

/**
 * Should be implemented for commands expected results (for example, enrich sagaData with new data from reply
 * message body).
 *
 * @author Oleg Filatov <phpsagas@gmail.com>
 */
interface ReplyHandlerInterface
{
    /**
     * NOTE: Deserialize message payload inside the method.
     *
     * @param ReplyMessageInterface $message
     * @param SagaDataInterface     $sagaData
     */
    public function handle(ReplyMessageInterface $message, SagaDataInterface $sagaData): void;
}
