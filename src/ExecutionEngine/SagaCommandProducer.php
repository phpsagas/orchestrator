<?php

namespace PhpSagas\Orchestrator\ExecutionEngine;

use PhpSagas\Contracts\CommandMessageFactoryInterface;
use PhpSagas\Contracts\MessagePayloadSerializerInterface;
use PhpSagas\Contracts\MessageProducerInterface;
use PhpSagas\Contracts\SagaDataInterface;
use PhpSagas\Orchestrator\Command\RemoteCommandInterface;

/**
 * @author Oleg Filatov <phpsagas@gmail.com>
 */
class SagaCommandProducer implements SagaCommandProducerInterface
{
    /** @var MessageProducerInterface */
    private $messageProducer;
    /** @var CommandMessageFactoryInterface */
    private $messageFactory;
    /** @var MessagePayloadSerializerInterface */
    private $payloadSerializer;

    public function __construct(
        MessageProducerInterface $messageProducer,
        CommandMessageFactoryInterface $messageFactory,
        MessagePayloadSerializerInterface $payloadSerializer
    ) {
        $this->messageProducer = $messageProducer;
        $this->messageFactory = $messageFactory;
        $this->payloadSerializer = $payloadSerializer;
    }

    /**
     * @inheritDoc
     */
    public function send(
        string $sagaId,
        string $sagaType,
        SagaDataInterface $sagaData,
        RemoteCommandInterface $command,
        string $messageId
    ): void {
        $this->ensureSagaDataTypeValid($sagaData, $command);
        $payload = $this->payloadSerializer->serialize($command->getCommandData($sagaData));

        $commandType = $command->getCommandType();
        $message = $this->messageFactory->createCommandMessage($messageId, $sagaId, $sagaType, $commandType, $payload);

        $this->messageProducer->send($message);
    }

    /**
     * Prevents unexpected type data passing to $command::getCommandData method.
     *
     * @param SagaDataInterface      $sagaData
     * @param RemoteCommandInterface $command
     */
    private function ensureSagaDataTypeValid(SagaDataInterface $sagaData, RemoteCommandInterface $command): void
    {
        if (get_class($sagaData) !== $command->getSagaDataClassName()) {
            throw new \UnexpectedValueException(
                sprintf(
                    'Remote command %s failed due to unknown saga data type. Expected: %s, got: %s',
                    $command->getCommandType(),
                    $command->getSagaDataClassName(),
                    get_class($sagaData)
                )
            );
        }
    }
}
