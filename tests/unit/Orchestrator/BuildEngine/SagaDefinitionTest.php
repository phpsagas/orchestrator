<?php

namespace PhpSagas\Orchestrator\Tests;

use Codeception\Stub;
use Codeception\Test\Unit;
use PhpSagas\Contracts\ReplyMessageInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PhpSagas\Orchestrator\BuildEngine\ReplyHandlerInterface;
use PhpSagas\Contracts\SagaDataInterface;
use PhpSagas\Orchestrator\BuildEngine\SagaDefinition;
use PhpSagas\Orchestrator\BuildEngine\SagaExecutionState;
use PhpSagas\Orchestrator\BuildEngine\SagaStepInterface;

/**
 * @covers \PhpSagas\Orchestrator\BuildEngine\SagaDefinition
 *
 * @author Oleg Filatov <phpsagas@gmail.com>
 */
class SagaDefinitionTest extends Unit
{
    /**
     * @covers \PhpSagas\Orchestrator\BuildEngine\SagaDefinition::start
     * @throws \Exception
     */
    public function testStartWithEmptyStepsWorks(): void
    {
        /** @var SagaDataInterface $sagaData */
        $sagaData = Stub::makeEmpty(SagaDataInterface::class);
        $steps = [];
        $definition = new SagaDefinition($steps);

        $actions = $definition->start($sagaData);

        self::assertTrue($actions->isEndState());
    }

    /**
     * @covers \PhpSagas\Orchestrator\BuildEngine\SagaDefinition::start
     * @throws \Exception
     */
    public function testStartWithStepsWorks(): void
    {
        /** @var SagaDataInterface $sagaData */
        $sagaData = Stub::makeEmpty(SagaDataInterface::class);
        $steps = [Stub::makeEmpty(SagaStepInterface::class)];
        $definition = new SagaDefinition($steps);

        $actions = $definition->start($sagaData);

        self::assertFalse($actions->isEndState());
    }

    /**
     * @covers \PhpSagas\Orchestrator\BuildEngine\SagaDefinition::handleReply
     * @throws \Exception
     */
    public function testHandleSuccessReplyWorks(): void
    {
        /** @var ReplyHandlerInterface|MockObject $replyHandler */
        $replyHandler = Stub::makeEmpty(ReplyHandlerInterface::class);
        /** @var SagaStepInterface|MockObject $sagaStep */
        $sagaStep = Stub::makeEmpty(SagaStepInterface::class);
        /** @var SagaExecutionState|MockObject $executionState */
        $executionState = Stub::makeEmpty(SagaExecutionState::class);
        /** @var SagaDataInterface|MockObject $sagaData */
        $sagaData = Stub::makeEmpty(SagaDataInterface::class);
        /** @var ReplyMessageInterface|MockObject $replyMessage */
        $replyMessage = Stub::makeEmpty(ReplyMessageInterface::class);

        $definition = new SagaDefinition([$sagaStep]);

        $executionState->expects(self::atLeastOnce())->method('getCurrentStepIndex')->willReturn(0);
        $replyMessage->expects(self::once())->method('isSuccess')->willReturn(true);
        $executionState->expects(self::never())->method('startCompensating');
        $replyHandler->expects(self::once())->method('handle')->with($replyMessage, $sagaData);

        $definition->handleReply($executionState, $sagaData, $replyMessage);
    }

    /**
     * @covers \PhpSagas\Orchestrator\BuildEngine\SagaDefinition::handleReply
     * @throws \Exception
     */
    public function testHandleFailureReplyWorks(): void
    {
        /** @var ReplyHandlerInterface|MockObject $replyHandler */
        $replyHandler = Stub::makeEmpty(ReplyHandlerInterface::class);
        /** @var SagaStepInterface|MockObject $sagaStep */
        $sagaStep = Stub::makeEmpty(SagaStepInterface::class);
        /** @var SagaExecutionState|MockObject $executionState */
        $executionState = Stub::makeEmpty(SagaExecutionState::class);
        /** @var SagaDataInterface|MockObject $sagaData */
        $sagaData = Stub::makeEmpty(SagaDataInterface::class);
        /** @var ReplyMessageInterface|MockObject $replyMessage */
        $replyMessage = Stub::makeEmpty(ReplyMessageInterface::class);

        $definition = new SagaDefinition([$sagaStep]);

        $executionState->expects(self::atLeastOnce())->method('getCurrentStepIndex')->willReturn(0);
        $replyMessage->expects(self::once())->method('isSuccess')->willReturn(false);
        $replyHandler->expects(self::never())->method('handle')->with($replyMessage, $sagaData);
        $executionState->expects(self::once())->method('startCompensating');

        $definition->handleReply($executionState, $sagaData, $replyMessage);
    }
}
