<?php

namespace PhpSagas\Orchestrator\BuildEngine;

use PhpSagas\Contracts\SagaDataInterface;
use PhpSagas\Orchestrator\Command\RemoteCommandInterface;

/**
 * Step with remote command.
 *
 * @author Oleg Filatov <phpsagas@gmail.com>
 */
class RemoteStep implements SagaStepInterface
{
    /** @var RemoteCommandInterface */
    private $action;
    /** @var ReplyHandlerInterface|null */
    private $actionReplyHandler;
    /** @var RemoteCommandInterface|null */
    private $compensation;
    /** @var ReplyHandlerInterface|null */
    private $compensationReplyHandler;

    public function __construct(
        RemoteCommandInterface $action,
        ?ReplyHandlerInterface $actionReplyHandler,
        ?RemoteCommandInterface $compensation,
        ?ReplyHandlerInterface $compensationReplyHandler
    ) {
        $this->action = $action;
        $this->actionReplyHandler = $actionReplyHandler;
        $this->compensation = $compensation;
        $this->compensationReplyHandler = $compensationReplyHandler;
    }

    /**
     * @return bool
     */
    public function hasCompensation(): bool
    {
        return isset($this->compensation);
    }

    /**
     * @inheritDoc
     */
    public function execute(SagaDataInterface $sagaData, bool $isCompensating, SagaActions $actions): SagaActions
    {
        /** @var RemoteCommandInterface $action */
        $action = ($isCompensating ? $this->compensation : $this->action);
        return $actions->setRemote($action);
    }

    /**
     * @param bool $isCompensating
     *
     * @return ReplyHandlerInterface|null
     */
    public function getReplyHandler(bool $isCompensating): ?ReplyHandlerInterface
    {
        return ($isCompensating ? $this->compensationReplyHandler : $this->actionReplyHandler);
    }
}
