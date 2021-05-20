<?php

namespace PhpSagas\Orchestrator\BuildEngine;

use PhpSagas\Orchestrator\Command\RemoteCommandInterface;

/**
 * @author Oleg Filatov <phpsagas@gmail.com>
 */
class RemoteStepBuilder
{
    /** @var SagaDefinitionBuilder */
    private $definitionBuilder;
    /** @var RemoteCommandInterface */
    private $action;
    /** @var RemoteCommandInterface|null */
    private $compensation;
    /** @var ReplyHandlerInterface|null */
    private $actionReplyHandler;
    /** @var ReplyHandlerInterface|null */
    private $compensationReplyHandler;

    public function __construct(SagaDefinitionBuilder $builder, RemoteCommandInterface $command)
    {
        $this->action = $command;
        $this->definitionBuilder = $builder;
    }

    /**
     * @param RemoteCommandInterface $command
     *
     * @return RemoteStepBuilder
     */
    public function withCompensation(RemoteCommandInterface $command): RemoteStepBuilder
    {
        $this->compensation = $command;
        return $this;
    }

    /**
     * IMPORTANT: Be careful executing that method on saga definition - it can be used either to handle forward
     * transaction result or for compensation handling.
     *
     * @param ReplyHandlerInterface $replyHandler
     *
     * @return RemoteStepBuilder
     */
    public function onReply(ReplyHandlerInterface $replyHandler): self
    {
        if (isset($this->compensation)) {
            $this->compensationReplyHandler = $replyHandler;
        } else {
            $this->actionReplyHandler = $replyHandler;
        }

        return $this;
    }

    /**
     * @return StepBuilder
     */
    public function step(): StepBuilder
    {
        $this->addStep();
        return new StepBuilder($this->definitionBuilder);
    }

    /**
     * @return SagaDefinition
     */
    public function build(): SagaDefinition
    {
        $this->addStep();
        return $this->definitionBuilder->build();
    }

    /**
     * @return void
     */
    private function addStep(): void
    {
        $this->definitionBuilder->addStep(
            new RemoteStep(
                $this->action,
                $this->actionReplyHandler,
                $this->compensation,
                $this->compensationReplyHandler
            )
        );
    }
}
