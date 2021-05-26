<?php

namespace PhpSagas\Orchestrator\ExecutionEngine;

use PhpSagas\Contracts\MessageIdGeneratorInterface;
use PhpSagas\Contracts\ReplyMessageFactoryInterface;
use PhpSagas\Contracts\ReplyMessageInterface;
use PhpSagas\Contracts\SagaSerializerInterface;
use PhpSagas\Orchestrator\BuildEngine\SagaActions;
use PhpSagas\Contracts\SagaDataInterface;
use PhpSagas\Orchestrator\BuildEngine\SagaInterface;
use PhpSagas\Orchestrator\InstantiationEngine\SagaInstanceInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * @author Oleg Filatov <phpsagas@gmail.com>
 */
class SagaActionsProcessor implements SagaActionsProcessorInterface
{
    /** @var int */
    protected const MAX_CONSECUTIVE_LOCAL_COMMAND_ACTIONS = 10;

    /** @var SagaInstanceRepositoryInterface */
    private $sagaInstanceRepo;
    /** @var SagaSerializerInterface */
    private $sagaSerializer;
    /** @var SagaExecutionStateSerializerInterface */
    private $executionStateSerializer;
    /** @var SagaCommandProducerInterface */
    private $sagaCommandProducer;
    /** @var MessageIdGeneratorInterface */
    private $messageIdGenerator;
    /** @var SagaLockerInterface */
    private $sagaLocker;
    /** @var ReplyMessageFactoryInterface */
    private $replyMessageFactory;
    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        SagaInstanceRepositoryInterface $sagaInstanceRepo,
        SagaSerializerInterface $sagaSerializer,
        SagaExecutionStateSerializerInterface $executionStateSerializer,
        MessageIdGeneratorInterface $messageIdGenerator,
        SagaLockerInterface $sagaLocker,
        ReplyMessageFactoryInterface $replyMessageFactory,
        SagaCommandProducerInterface $sagaCommandProducer
    ) {
        $this->sagaInstanceRepo = $sagaInstanceRepo;
        $this->sagaSerializer = $sagaSerializer;
        $this->executionStateSerializer = $executionStateSerializer;
        $this->messageIdGenerator = $messageIdGenerator;
        $this->sagaCommandProducer = $sagaCommandProducer;
        $this->sagaLocker = $sagaLocker;
        $this->replyMessageFactory = $replyMessageFactory;
        $this->logger = new NullLogger();
    }

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function processActions(
        SagaInterface $saga,
        SagaInstanceInterface $sagaInstance,
        SagaDataInterface $sagaData,
        SagaActions $actions
    ): void {
        $counter = 0;
        $sagaId = $sagaInstance->getSagaId();
        $this->logger->info('saga {saga_id} processing', ['saga_id' => $sagaId]);

        while ($counter++ < static::MAX_CONSECUTIVE_LOCAL_COMMAND_ACTIONS) {
            $exception = $actions->getLocalException();

            if (isset($exception)) {
                $executionState = $this->executionStateSerializer->deserialize($actions->getExecutionStateOrFail());
                $actions = $saga->getSagaDefinition()->handleReply(
                    $executionState,
                    $actions->getUpdatedSagaDataOrFail(),
                    $this->makeEmptyFailureReplyMessage($sagaId)
                );
            } else {
                $executionState = $actions->getExecutionState();

                if (isset($executionState)) {
                    $sagaInstance->setExecutionState($executionState);
                }

                /** @var SagaDataInterface $updatedSagaData */
                $updatedSagaData = ($actions->getUpdatedSagaData() ?? $sagaData);
                $sagaInstance->setSagaData(
                    $this->sagaSerializer->serialize($updatedSagaData),
                    get_class($updatedSagaData)
                );

                if ($actions->isEndState()) {
                    if ($actions->isCompensating()) {
                        $sagaInstance->setStatus(SagaStatusEnum::FAILED);
                        $this->sagaInstanceRepo->saveSaga($sagaInstance);
                        $saga->onFailed($sagaId, $updatedSagaData);
                        $this->logger->info('saga {saga_id} failed', ['saga_id' => $sagaId]);
                    } else {
                        $sagaInstance->setStatus(SagaStatusEnum::FINISHED);
                        $this->sagaInstanceRepo->saveSaga($sagaInstance);
                        $saga->onFinished($sagaId, $updatedSagaData);
                        $this->logger->info('saga {saga_id} successfully finished', ['saga_id' => $sagaId]);
                    }

                    $this->sagaLocker->unlock($sagaInstance);
                    break;
                }
                /**
                 * Generate msgId and save saga before message sending for race condition preventing
                 * (exclude variant message will be handled earlier than saga with new msgId will be saved into DB).
                 */
                $messageId = $this->messageIdGenerator->generateId();
                $sagaInstance->setLastMessageId($messageId);
                $sagaInstance->setStatus(SagaStatusEnum::RUNNING);

                $this->sagaInstanceRepo->saveSaga($sagaInstance);

                if ($actions->isLocal()) {
                    $executionState = $this->executionStateSerializer->deserialize($actions->getExecutionStateOrFail());
                    $actions = $saga->getSagaDefinition()->handleReply(
                        $executionState,
                        $updatedSagaData,
                        $this->makeEmptySuccessReplyMessage($sagaId)
                    );
                } else {
                    $command = $actions->getCommand();
                    $sagaType = $saga->getSagaType();
                    $this->sagaCommandProducer->send($sagaId, $sagaType, $updatedSagaData, $command, $messageId);
                    $this->logger->info(
                        'saga {saga_id} send command {command_type}',
                        ['saga_id' => $sagaId, 'command_type' => $command->getCommandType()]
                    );
                    break;
                }
            }
        }
    }

    /**
     * @param string $sagaId
     *
     * @return ReplyMessageInterface
     */
    private function makeEmptyFailureReplyMessage(string $sagaId): ReplyMessageInterface
    {
        return $this->replyMessageFactory->makeFailure($sagaId, '', '{}');
    }

    /**
     * @param string $sagaId
     *
     * @return ReplyMessageInterface
     */
    private function makeEmptySuccessReplyMessage(string $sagaId): ReplyMessageInterface
    {
        return $this->replyMessageFactory->makeSuccess($sagaId, '', '{}');
    }
}
