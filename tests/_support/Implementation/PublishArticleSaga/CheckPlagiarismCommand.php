<?php

namespace PhpSagas\Orchestrator\Tests\_support\Implementation\PublishArticleSaga;

use PhpSagas\Contracts\SagaDataInterface;
use PhpSagas\Orchestrator\Command\LocalCommandException;
use PhpSagas\Orchestrator\Command\LocalCommandInterface;

/**
 * @author Oleg Filatov <phpsagas@gmail.com>
 */
class CheckPlagiarismCommand implements LocalCommandInterface
{
    /** @var bool */
    private $failed;

    public function __construct(bool $failed)
    {
        $this->failed = $failed;
    }

    public function execute(SagaDataInterface $sagaData): void
    {
        if ($this->failed) {
            throw new LocalCommandException();
        }
    }

    public function getSagaDataType(): string
    {
        return PublishArticleSagaData::class;
    }
}
