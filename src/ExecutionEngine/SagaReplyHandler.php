<?php

namespace PhpSagas\Orchestrator\ExecutionEngine;

use PhpSagas\Common\Message\ReplyMessage;
use PhpSagas\Orchestrator\InstantiationEngine\SagaFactoryInterface;
use PhpSagas\Orchestrator\InstantiationEngine\SagaInstanceInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Handles saga remote commands replies.
 *
 * @author Oleg Filatov <phpsagas@gmail.com>
 */
class SagaReplyHandler
{
    /** @var SagaInstanceRepositoryInterface */
    private $sagaInstanceRepo;
    /** @var SagaSerializerInterface */
    private $sagaSerializer;
    /** @var SagaActionsProcessorInterface */
    private $sagaActionsProcessor;
    /** @var SagaFactoryInterface */
    private $sagaFactory;
    /** @var SagaExecutionStateSerializerInterface */
    private $executionStateSerializer;
    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        SagaInstanceRepositoryInterface $sagaInstanceRepo,
        SagaSerializerInterface $sagaSerializer,
        SagaExecutionStateSerializerInterface $executionStateSerializer,
        SagaFactoryInterface $sagaFactory,
        SagaActionsProcessorInterface $sagaActionsProcessor
    ) {
        $this->sagaInstanceRepo = $sagaInstanceRepo;
        $this->sagaSerializer = $sagaSerializer;
        $this->executionStateSerializer = $executionStateSerializer;
        $this->sagaActionsProcessor = $sagaActionsProcessor;
        $this->sagaFactory = $sagaFactory;
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
     * @param ReplyMessage $message
     */
    public function handleReply(ReplyMessage $message): void
    {
        $this->logger->info(
            'saga {saga_id} reply handler started on message {message_id}',
            ['saga_id' => $message->getSagaId(), 'message_id' => $message->getId()]
        );
        $sagaInstance = $this->sagaInstanceRepo->findSaga($message->getSagaId());

        $this->ensureReplyMessageExpected($sagaInstance, $message);

        $initialDataType = $sagaInstance->getInitialDataType();
        $initialData = isset($initialDataType)
            ? $this->sagaSerializer->deserialize($sagaInstance->getInitialData(), $initialDataType)
            : null
        ;
        $saga = $this->sagaFactory->create($sagaInstance->getSagaType(), $initialData);

        $sagaData = $this->sagaSerializer->deserialize($sagaInstance->getSagaData(), $sagaInstance->getSagaDataType());
        $serializedState = $sagaInstance->getExecutionState();

        $executionState = $this->executionStateSerializer->deserialize($serializedState);
        // NOTE: sagaData can be changed inside concrete handler
        $actions = $saga->getSagaDefinition()->handleReply($executionState, $sagaData, $message);

        $this->logger->info(
            'saga {saga_id} reply handler finished on message {message_id}',
            ['saga_id' => $message->getSagaId(), 'message_id' => $message->getId()]
        );
        $this->sagaActionsProcessor->processActions($saga, $sagaInstance, $sagaData, $actions);
    }

    /**
     * Make sure given message is reply for last sending message (i.e. saga steps consistency has not been broken).
     *
     * @param SagaInstanceInterface $sagaInstance
     * @param ReplyMessage          $message
     */
    protected function ensureReplyMessageExpected(SagaInstanceInterface $sagaInstance, ReplyMessage $message): void
    {
        if ($sagaInstance->getLastMessageId() !== $message->getCorrelationId()) {
            throw new HandleReplyFailedException(
                sprintf(
                    'Unexpected message: %s. Correlation id: %s. Last message id: %s',
                    $message->getId(),
                    $message->getCorrelationId(),
                    $sagaInstance->getLastMessageId()
                )
            );
        }
    }
}
