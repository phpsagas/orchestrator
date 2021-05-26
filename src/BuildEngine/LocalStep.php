<?php

namespace PhpSagas\Orchestrator\BuildEngine;

use PhpSagas\Contracts\SagaDataInterface;
use PhpSagas\Orchestrator\Command\LocalCommandException;
use PhpSagas\Orchestrator\Command\LocalCommandInterface;

/**
 * Step with local command.
 *
 * @author Oleg Filatov <phpsagas@gmail.com>
 */
class LocalStep implements SagaStepInterface
{
    /** @var LocalCommandInterface */
    private $command;
    /** @var LocalCommandInterface|null */
    private $compensation;

    public function __construct(LocalCommandInterface $command, ?LocalCommandInterface $compensation)
    {
        $this->command = $command;
        $this->compensation = $compensation;
    }

    /**
     * @return bool
     */
    public function hasCompensation(): bool
    {
        return isset($this->compensation);
    }

    /**
     * @inheritDoc
     */
    public function execute(SagaDataInterface $sagaData, bool $isCompensating, SagaActions $actions): SagaActions
    {
        $command = (!$isCompensating ? $this->command : $this->compensation);

        try {
            if (isset($command)) {
                $this->ensureSagaDataTypeValid($sagaData, $command);
                $command->execute($sagaData);
            }

            return $actions->setLocal();
        } catch (LocalCommandException $e) {
            return $actions->setLocal($e);
        }
    }

    /**
     * There is no need to handle local command result separately because it can be done there.
     *
     * @param bool $isCompensating
     *
     * @return ReplyHandlerInterface|null
     */
    public function getReplyHandler(bool $isCompensating): ?ReplyHandlerInterface
    {
        return null;
    }

    /**
     * @param SagaDataInterface      $sagaData
     * @param LocalCommandInterface $command
     */
    private function ensureSagaDataTypeValid($sagaData, LocalCommandInterface $command): void
    {
        if ($command->getSagaDataType() && get_class($sagaData) !== $command->getSagaDataType()) {
            throw new \UnexpectedValueException(
                sprintf(
                    'Local command %s failed due to unknown saga data type. Expected: %s, got: %s',
                    get_class($command),
                    $command->getSagaDataType(),
                    get_class($sagaData)
                )
            );
        }
    }
}
