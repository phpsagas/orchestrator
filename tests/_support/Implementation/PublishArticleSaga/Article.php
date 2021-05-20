<?php

namespace PhpSagas\Orchestrator\Tests\_support\Implementation\PublishArticleSaga;

/**
 * @author Oleg Filatov <phpsagas@gmail.com>
 */
class Article
{
    /** @var string */
    public const STATUS_APPROVED = 'approved';
    /** @var string */
    public const STATUS_PENDING = 'pending';
    /** @var string */
    public const STATUS_REJECTED = 'rejected';

    /** @var string */
    private $title;
    /** @var string */
    private $body;
    /** @var string */
    private $status;

    public function __construct(string $title, string $body)
    {
        $this->title = $title;
        $this->body = $body;
        $this->status = self::STATUS_PENDING;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getBody(): string
    {
        return $this->body;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    public function approve(): void
    {
        $this->status = self::STATUS_APPROVED;
    }

    public function reject(): void
    {
        $this->status = self::STATUS_REJECTED;
    }
}
