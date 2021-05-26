<?php

namespace PhpSagas\Orchestrator\Tests\_support\Implementation\PublishArticleSaga;

use PhpSagas\Contracts\SagaDataInterface;
use PhpSagas\Orchestrator\Command\LocalCommandInterface;

/**
 * @author Oleg Filatov <phpsagas@gmail.com>
 */
class RejectArticleCommand implements LocalCommandInterface
{
    /**
     * @param SagaDataInterface|PublishArticleSagaData $sagaData
     */
    public function execute(SagaDataInterface $sagaData): void
    {
        $article = ArticleRepository::getInstance()->findByTitle($sagaData->getArticleTitle());
        $article->reject();
        ArticleRepository::getInstance()->save($article);
    }

    public function getSagaDataType(): string
    {
        return PublishArticleSagaData::class;
    }
}
