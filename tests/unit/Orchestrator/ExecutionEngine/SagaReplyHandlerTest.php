<?php

namespace PhpSagas\Orchestrator\Tests;

use Codeception\Test\Unit;
use Codeception\Util\Stub;
use PHPUnit\Framework\MockObject\MockObject;
use PhpSagas\Common\Message\ReplyMessage;
use PhpSagas\Orchestrator\ExecutionEngine\HandleReplyFailedException;
use PhpSagas\Orchestrator\ExecutionEngine\SagaActionsProcessor;
use PhpSagas\Orchestrator\ExecutionEngine\SagaExecutionStateSerializerInterface;
use PhpSagas\Orchestrator\ExecutionEngine\SagaInstanceRepositoryInterface;
use PhpSagas\Orchestrator\ExecutionEngine\SagaReplyHandler;
use PhpSagas\Orchestrator\ExecutionEngine\SagaSerializerInterface;
use PhpSagas\Orchestrator\InstantiationEngine\SagaFactoryInterface;
use PhpSagas\Orchestrator\InstantiationEngine\SagaInstanceInterface;

/**
 * @covers \PhpSagas\Orchestrator\ExecutionEngine\SagaReplyHandler
 *
 * @author Oleg Filatov <phpsagas@gmail.com>
 */
class SagaReplyHandlerTest extends Unit
{
    /**
     * @covers \PhpSagas\Orchestrator\ExecutionEngine\SagaReplyHandler::handleReply
     * @throws \Exception
     */
    public function testHandleUnexpectedMessageFailed(): void
    {
        /** @var SagaInstanceRepositoryInterface|MockObject $sagaInstanceRepo */
        $sagaInstanceRepo = Stub::makeEmpty(
            SagaInstanceRepositoryInterface::class,
            ['findSaga' => Stub::makeEmpty(SagaInstanceInterface::class, ['getLastMessageId' => '1'])]
        );
        /** @var SagaSerializerInterface|MockObject $sagaSerializer */
        $sagaSerializer = Stub::makeEmpty(SagaSerializerInterface::class);
        /** @var SagaExecutionStateSerializerInterface|MockObject $executionStateSerializer */
        $executionStateSerializer = Stub::makeEmpty(SagaExecutionStateSerializerInterface::class);
        /** @var SagaFactoryInterface|MockObject $sagaFactory */
        $sagaFactory = Stub::makeEmpty(SagaFactoryInterface::class);
        /** @var SagaActionsProcessor|MockObject $sagaActionsProcessor */
        $sagaActionsProcessor = Stub::makeEmpty(SagaActionsProcessor::class);
        /** @var ReplyMessage|MockObject $message */
        $message = Stub::makeEmpty(ReplyMessage::class, ['getCorrelationId' => '2']);

        $this->expectException(HandleReplyFailedException::class);

        $handler = new SagaReplyHandler(
            $sagaInstanceRepo,
            $sagaSerializer,
            $executionStateSerializer,
            $sagaFactory,
            $sagaActionsProcessor
        );
        $handler->handleReply($message);
    }

    /**
     * @covers \PhpSagas\Orchestrator\ExecutionEngine\SagaReplyHandler::handleReply
     * @throws \Exception
     */
    public function testHandleExpectedMessageWorks(): void
    {
        /** @var SagaInstanceRepositoryInterface|MockObject $sagaInstanceRepo */
        $sagaInstanceRepo = Stub::makeEmpty(
            SagaInstanceRepositoryInterface::class,
            ['findSaga' => Stub::makeEmpty(SagaInstanceInterface::class, ['getLastMessageId' => '1'])]
        );
        /** @var SagaSerializerInterface|MockObject $sagaSerializer */
        $sagaSerializer = Stub::makeEmpty(SagaSerializerInterface::class);
        /** @var SagaExecutionStateSerializerInterface|MockObject $executionStateSerializer */
        $executionStateSerializer = Stub::makeEmpty(SagaExecutionStateSerializerInterface::class);
        /** @var SagaFactoryInterface|MockObject $sagaFactory */
        $sagaFactory = Stub::makeEmpty(SagaFactoryInterface::class);
        /** @var SagaActionsProcessor|MockObject $sagaActionsProcessor */
        $sagaActionsProcessor = Stub::makeEmpty(SagaActionsProcessor::class);
        /** @var ReplyMessage|MockObject $message */
        $message = Stub::makeEmpty(ReplyMessage::class, ['getCorrelationId' => '1']);

        $sagaActionsProcessor->expects(self::once())->method('processActions');

        $handler = new SagaReplyHandler(
            $sagaInstanceRepo,
            $sagaSerializer,
            $executionStateSerializer,
            $sagaFactory,
            $sagaActionsProcessor
        );
        $handler->handleReply($message);
    }
}
