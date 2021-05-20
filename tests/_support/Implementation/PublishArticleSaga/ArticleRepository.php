<?php

namespace PhpSagas\Orchestrator\Tests\_support\Implementation\PublishArticleSaga;

/**
 * @author Oleg Filatov <phpsagas@gmail.com>
 */
class ArticleRepository
{
    /** @var self */
    private static $instance;

    /** @var Article[] */
    private $articles;

    private function __construct()
    {
    }

    public static function getInstance(): self
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function save(Article $article)
    {
        $this->articles[$article->getTitle()] = $article;
    }

    public function findByTitle(string $title): ?Article
    {
        return $this->articles[$title] ?? null;
    }

    public function reset(): void
    {
        $this->articles = [];
    }
}
