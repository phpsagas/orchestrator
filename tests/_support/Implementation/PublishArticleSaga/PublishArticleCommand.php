<?php

namespace PhpSagas\Orchestrator\Tests\_support\Implementation\PublishArticleSaga;

use PhpSagas\Contracts\SagaDataInterface;
use PhpSagas\Orchestrator\Command\LocalCommandInterface;

/**
 * @author Oleg Filatov <phpsagas@gmail.com>
 */
class PublishArticleCommand implements LocalCommandInterface
{
    /**
     * @param SagaDataInterface|PublishArticleSagaData $sagaData
     */
    public function execute(SagaDataInterface $sagaData): void
    {
        $article = new Article($sagaData->getArticleTitle(), $sagaData->getArticleBody());
        ArticleRepository::getInstance()->save($article);
    }

    public function getSagaDataType(): string
    {
        return PublishArticleSagaData::class;
    }
}
