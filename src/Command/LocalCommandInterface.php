<?php

namespace PhpSagas\Orchestrator\Command;

use PhpSagas\Contracts\SagaDataInterface;

/**
 * Should be implemented by commands execute some logic in _current_ project.
 * Used when orchestrator installed as part of project packages (i.e. not standalone).
 *
 * @author Oleg Filatov <phpsagas@gmail.com>
 */
interface LocalCommandInterface
{
    /**
     * Return a type expected self::execute input params.
     *
     * @return string
     */
    public function getSagaDataType(): string;

    /**
     * @param $sagaData
     *
     * @throws LocalCommandException
     */
    public function execute(SagaDataInterface $sagaData): void;
}
