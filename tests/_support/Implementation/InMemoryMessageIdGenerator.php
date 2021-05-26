<?php

namespace PhpSagas\Orchestrator\Tests\_support\Implementation;

use PhpSagas\Contracts\MessageIdGeneratorInterface;

/**
 * @author Oleg Filatov <phpsagas@gmail.com>
 */
class InMemoryMessageIdGenerator implements MessageIdGeneratorInterface
{
    /** @var int */
    private $nextId = 1;

    public function generateId(): string
    {
        return (string)$this->nextId++;
    }

    public function reset(): void
    {
        $this->nextId = 1;
    }
}
