<?php

namespace PhpSagas\Orchestrator\Tests\_support\Implementation\PublishArticleSaga;

use PhpSagas\Orchestrator\BuildEngine\SagaDataInterface;

/**
 * @author Oleg Filatov <phpsagas@gmail.com>
 */
class PublishArticleSagaData implements SagaDataInterface, \JsonSerializable
{
    /** @var string */
    private $articleTitle;
    /** @var string */
    private $articleBody;

    public function __construct(string $articleTitle, string $articleBody)
    {
        $this->articleTitle = $articleTitle;
        $this->articleBody = $articleBody;
    }

    /**
     * @return string
     */
    public function getArticleTitle(): string
    {
        return $this->articleTitle;
    }

    /**
     * @return string
     */
    public function getArticleBody(): string
    {
        return $this->articleBody;
    }

    public function jsonSerialize()
    {
        return [
            'articleTitle' => $this->articleTitle,
            'articleBody' => $this->articleBody,
        ];
    }
}
