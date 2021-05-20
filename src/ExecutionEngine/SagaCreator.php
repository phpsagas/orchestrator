<?php

namespace PhpSagas\Orchestrator\ExecutionEngine;

use PhpSagas\Orchestrator\BuildEngine\SagaDataInterface;
use PhpSagas\Orchestrator\BuildEngine\SagaInterface;
use PhpSagas\Orchestrator\InstantiationEngine\SagaInstanceFactoryInterface;
use PhpSagas\Orchestrator\InstantiationEngine\SagaInstanceInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Creates and starts saga execution.
 *
 * @author Oleg Filatov <phpsagas@gmail.com>
 */
class SagaCreator
{
    /** @var SagaInstanceRepositoryInterface */
    private $sagaInstanceRepo;
    /** @var SagaInstanceFactoryInterface */
    private $sagaInstanceFactory;
    /** @var SagaActionsProcessorInterface */
    private $sagaActionsProcessor;
    /** @var SagaLockerInterface */
    private $sagaLocker;
    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        SagaInstanceRepositoryInterface $sagaInstanceRepo,
        SagaInstanceFactoryInterface $sagaInstanceFactory,
        SagaLockerInterface $sagaLocker,
        SagaActionsProcessorInterface $sagaActionsProcessor
    ) {
        $this->sagaInstanceRepo = $sagaInstanceRepo;
        $this->sagaInstanceFactory = $sagaInstanceFactory;
        $this->sagaActionsProcessor = $sagaActionsProcessor;
        $this->sagaLocker = $sagaLocker;
        $this->logger = new NullLogger();
    }

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    public function create(SagaInterface $saga, SagaDataInterface $sagaData): SagaInstanceInterface
    {
        $sagaInstance = $this->sagaInstanceFactory->makeSagaInstance($saga, $sagaData);
        $sagaId = $this->sagaInstanceRepo->saveSaga($sagaInstance);
        $this->logger->info('saga {saga_id} created', ['saga_id' => $sagaId]);

        $this->sagaLocker->lock($sagaInstance);

        $saga->onStarted($sagaId, $sagaData);
        $actions = $saga->getSagaDefinition()->start($sagaData);
        $this->logger->info('saga {saga_id} started', ['saga_id' => $sagaId]);

        $exception = $actions->getLocalException();
        if (isset($exception)) {
            $this->logger->info('saga {saga_id} stopped by first command exception', ['saga_id' => $sagaId]);
            throw $exception;
        }

        $this->sagaActionsProcessor->processActions($saga, $sagaInstance, $sagaData, $actions);

        return $sagaInstance;
    }
}
