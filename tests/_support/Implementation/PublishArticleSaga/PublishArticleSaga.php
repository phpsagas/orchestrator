<?php

namespace PhpSagas\Orchestrator\Tests\_support\Implementation\PublishArticleSaga;

use PhpSagas\Contracts\SagaDataInterface;
use PhpSagas\Orchestrator\BuildEngine\SagaDefinition;
use PhpSagas\Orchestrator\BuildEngine\SagaDefinitionBuilder;
use PhpSagas\Orchestrator\BuildEngine\SagaInterface;
use PhpSagas\Orchestrator\BuildEngine\StepBuilder;
use PhpSagas\Orchestrator\Tests\_support\Implementation\SagaExecutionDetectorInterface;

/**
 * @author Oleg Filatov <phpsagas@gmail.com>
 */
class PublishArticleSaga implements SagaInterface
{
    /** @var SagaDefinition */
    private $definition;
    /** @var SagaDataInterface|null */
    private $initialData;
    /** @var SagaExecutionDetectorInterface */
    private $executionDetector;

    public function __construct(
        CheckPlagiarismCommand $checkPlagiarismCommand,
        ?SagaDataInterface $initialData,
        SagaExecutionDetectorInterface $detector
    ) {
        $this->initialData = $initialData;
        $this->executionDetector = $detector;

        $steps = $this
            ->step()
            ->localCommand(new PublishArticleCommand())
            ->withCompensation(new RejectArticleCommand())
            ->step()
            ->localCommand($checkPlagiarismCommand)
            ->step()
            ->localCommand(new ApproveArticleCommand());

        $this->definition = $steps->build();
    }

    public function getSagaDefinition(): SagaDefinition
    {
        return $this->definition;
    }

    public function getSagaType(): string
    {
        return 'publish_article_saga';
    }

    public function getSagaInitialData(): ?SagaDataInterface
    {
        return $this->initialData;
    }

    public function onStarted(string $sagaId, SagaDataInterface $data): void
    {
        $this->executionDetector->starting();
    }

    public function onFinished(string $sagaId, SagaDataInterface $data): void
    {
        $this->executionDetector->finished();
    }

    public function onFailed(string $sagaId, SagaDataInterface $data): void
    {
        $this->executionDetector->failed();
    }

    private function step(): StepBuilder
    {
        return new StepBuilder(new SagaDefinitionBuilder());
    }
}
