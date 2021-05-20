<?php

namespace PhpSagas\Orchestrator\BuildEngine;

use PhpSagas\Orchestrator\Command\LocalCommandInterface;

/**
 * @author Oleg Filatov <phpsagas@gmail.com>
 */
class LocalStepBuilder
{
    /** @var SagaDefinitionBuilder */
    private $definitionBuilder;
    /** @var LocalCommandInterface */
    private $command;
    /** @var LocalCommandInterface|null */
    private $compensation;

    public function __construct(SagaDefinitionBuilder $parent, LocalCommandInterface $command)
    {
        $this->definitionBuilder = $parent;
        $this->command = $command;
    }

    /**
     * @return StepBuilder
     */
    public function step(): StepBuilder
    {
        $this->definitionBuilder->addStep(new LocalStep($this->command, $this->compensation));
        return new StepBuilder($this->definitionBuilder);
    }

    /**
     * @param LocalCommandInterface $command
     *
     * @return LocalStepBuilder
     */
    public function withCompensation(LocalCommandInterface $command): LocalStepBuilder
    {
        $this->compensation = $command;
        return $this;
    }

    /**
     * @return SagaDefinition
     */
    public function build(): SagaDefinition
    {
        $this->definitionBuilder->addStep(new LocalStep($this->command, $this->compensation));
        return $this->definitionBuilder->build();
    }
}
