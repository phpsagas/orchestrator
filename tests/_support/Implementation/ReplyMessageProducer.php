<?php

namespace PhpSagas\Orchestrator\Tests\_support\Implementation;

use PhpSagas\Contracts\ReplyMessageInterface;
use PhpSagas\Orchestrator\ExecutionEngine\SagaReplyHandler;

/**
 * @author Oleg Filatov <phpsagas@gmail.com>
 */
class ReplyMessageProducer
{
    /** @var SagaReplyHandler */
    private $sagaReplyHandler;

    public function __construct(SagaReplyHandler $sagaReplyHandler = null)
    {
        $this->sagaReplyHandler = $sagaReplyHandler;
    }

    public function setSagaReplyHandler(SagaReplyHandler $sagaReplyHandler): ReplyMessageProducer
    {
        $this->sagaReplyHandler = $sagaReplyHandler;
        return $this;
    }

    public function send(ReplyMessageInterface $message): void
    {
        $this->sagaReplyHandler->handleReply($message);
    }
}
