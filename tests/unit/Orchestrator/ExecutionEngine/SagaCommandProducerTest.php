<?php

namespace PhpSagas\Orchestrator\Tests;

use Codeception\Test\Unit;
use Codeception\Util\Stub;
use PHPUnit\Framework\MockObject\MockObject;
use PhpSagas\Common\Message\CommandMessage;
use PhpSagas\Common\Message\CommandMessageFactoryInterface;
use PhpSagas\Orchestrator\BuildEngine\SagaDataInterface;
use PhpSagas\Orchestrator\Command\RemoteCommandInterface;
use PhpSagas\Orchestrator\ExecutionEngine\MessagePayloadSerializerInterface;
use PhpSagas\Orchestrator\ExecutionEngine\MessageProducerInterface;
use PhpSagas\Orchestrator\ExecutionEngine\SagaCommandProducer;

/**
 * @covers \PhpSagas\Orchestrator\ExecutionEngine\SagaCommandProducer
 *
 * @author Oleg Filatov <phpsagas@gmail.com>
 */
class SagaCommandProducerTest extends Unit
{
    /**
     * @covers \PhpSagas\Orchestrator\ExecutionEngine\SagaCommandProducer::send
     * @throws \Exception
     */
    public function testSendInvalidSagaDataTypeFailed(): void
    {
        /** @var MessageProducerInterface|MockObject $messageProducer */
        $messageProducer = Stub::makeEmpty(MessageProducerInterface::class);
        /** @var CommandMessageFactoryInterface|MockObject $messageFactory */
        $messageFactory = Stub::makeEmpty(CommandMessageFactoryInterface::class);
        /** @var MessagePayloadSerializerInterface|MockObject $serializer */
        $serializer = Stub::makeEmpty(MessagePayloadSerializerInterface::class);
        /** @var SagaDataInterface|MockObject $sagaData */
        $sagaData = Stub::makeEmpty(SagaDataInterface::class);
        /** @var RemoteCommandInterface|MockObject $command */
        $command = Stub::makeEmpty(RemoteCommandInterface::class, ['getSagaDataClassName' => 'no_saga_data_class']);

        $this->expectException(\UnexpectedValueException::class);

        $producer = new SagaCommandProducer($messageProducer, $messageFactory, $serializer);
        $producer->send('dummy id', 'dummy type', $sagaData, $command, '1');
    }

    /**
     * @covers \PhpSagas\Orchestrator\ExecutionEngine\SagaCommandProducer::send
     * @throws \Exception
     */
    public function testSendCommandWorks(): void
    {
        /** @var MessageProducerInterface|MockObject $messageProducer */
        $messageProducer = Stub::makeEmpty(MessageProducerInterface::class);
        /** @var CommandMessageFactoryInterface|MockObject $messageFactory */
        $messageFactory = Stub::makeEmpty(CommandMessageFactoryInterface::class);
        /** @var MessagePayloadSerializerInterface|MockObject $serializer */
        $serializer = Stub::makeEmpty(MessagePayloadSerializerInterface::class);
        /** @var SagaDataInterface|MockObject $sagaData */
        $sagaData = Stub::makeEmpty(SagaDataInterface::class);
        /** @var RemoteCommandInterface|MockObject $command */
        $command = Stub::makeEmpty(RemoteCommandInterface::class, ['getSagaDataClassName' => get_class($sagaData)]);
        /** @var CommandMessage|MockObject $commandMessage */
        $commandMessage = Stub::makeEmpty(CommandMessage::class);

        $messageFactory->expects(self::once())->method('createCommandMessage')->willReturn($commandMessage);
        $messageProducer->expects(self::once())->method('send')->with($commandMessage);

        $producer = new SagaCommandProducer($messageProducer, $messageFactory, $serializer);
        $producer->send('dummy id', 'dummy type', $sagaData, $command, '1');
    }
}
