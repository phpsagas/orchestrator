<?php

namespace PhpSagas\Orchestrator\BuildEngine;

use PhpSagas\Common\Message\ReplyMessage;

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
     * @param ReplyMessage      $message
     * @param SagaDataInterface $sagaData
     */
    public function handle(ReplyMessage $message, SagaDataInterface $sagaData): void;
}
