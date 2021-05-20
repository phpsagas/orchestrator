<?php

namespace PhpSagas\Orchestrator\BuildEngine;

use PhpSagas\Orchestrator\Command\LocalCommandInterface;
use PhpSagas\Orchestrator\Command\NullCommand;
use PhpSagas\Orchestrator\Command\RemoteCommandInterface;

/**
 * @author Oleg Filatov <phpsagas@gmail.com>
 */
class StepBuilder
{
    /** @var SagaDefinitionBuilder */
    private $definitionBuilder;

    public function __construct(SagaDefinitionBuilder $builder)
    {
        $this->definitionBuilder = $builder;
    }

    /**
     * @param LocalCommandInterface $command
     *
     * @return LocalStepBuilder
     */
    public function localCommand(LocalCommandInterface $command): LocalStepBuilder
    {
        return new LocalStepBuilder($this->definitionBuilder, $command);
    }

    /**
     * @param RemoteCommandInterface $command
     *
     * @return RemoteStepBuilder
     */
    public function remoteCommand(RemoteCommandInterface $command): RemoteStepBuilder
    {
        return new RemoteStepBuilder($this->definitionBuilder, $command);
    }

    /**
     * May be used for compensate actions done before saga started.
     *
     * @param LocalCommandInterface $command
     *
     * @return LocalStepBuilder
     */
    public function withLocalCompensation(LocalCommandInterface $command): LocalStepBuilder
    {
        return (new LocalStepBuilder($this->definitionBuilder, new NullCommand()))->withCompensation($command);
    }
}
