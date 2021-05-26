<?php

namespace PhpSagas\Orchestrator\Tests;

use Codeception\Test\Unit;
use Codeception\Util\Stub;
use PhpSagas\Contracts\MessageIdGeneratorInterface;
use PhpSagas\Contracts\ReplyMessageFactoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PhpSagas\Orchestrator\BuildEngine\SagaActions;
use PhpSagas\Contracts\SagaDataInterface;
use PhpSagas\Orchestrator\BuildEngine\SagaDefinition;
use PhpSagas\Orchestrator\BuildEngine\SagaInterface;
use PhpSagas\Orchestrator\Command\RemoteCommandInterface;
use PhpSagas\Orchestrator\ExecutionEngine\SagaActionsProcessor;
use PhpSagas\Orchestrator\ExecutionEngine\SagaCommandProducerInterface;
use PhpSagas\Orchestrator\ExecutionEngine\SagaExecutionStateSerializerInterface;
use PhpSagas\Orchestrator\ExecutionEngine\SagaInstanceRepositoryInterface;
use PhpSagas\Orchestrator\ExecutionEngine\SagaLockerInterface;
use PhpSagas\Contracts\SagaSerializerInterface;
use PhpSagas\Orchestrator\ExecutionEngine\SagaStatusEnum;
use PhpSagas\Orchestrator\InstantiationEngine\SagaInstanceInterface;

/**
 * @covers \PhpSagas\Orchestrator\ExecutionEngine\SagaActionsProcessor
 *
 * @author Oleg Filatov <phpsagas@gmail.com>
 */
class SagaActionsProcessorTest extends Unit
{
    /**
     * @covers \PhpSagas\Orchestrator\ExecutionEngine\SagaActionsProcessor::processActions
     * @throws \Exception
     */
    public function testProcessActionsWithRemoteCommandWorks(): void
    {
        /** @var SagaInstanceRepositoryInterface|MockObject $instanceRepo */
        $instanceRepo = Stub::makeEmpty(SagaInstanceRepositoryInterface::class);
        /** @var SagaSerializerInterface|MockObject $sagaSerializer */
        $sagaSerializer = Stub::makeEmpty(SagaSerializerInterface::class);
        /** @var SagaCommandProducerInterface|MockObject $commandProducer */
        $commandProducer = Stub::makeEmpty(SagaCommandProducerInterface::class);
        /** @var SagaInterface|MockObject $saga */
        $saga = Stub::makeEmpty(SagaInterface::class);
        /** @var SagaInstanceInterface|MockObject $sagaInstance */
        $sagaInstance = Stub::makeEmpty(SagaInstanceInterface::class, ['getSagaId' => 'dummy id']);
        /** @var SagaDataInterface|MockObject $sagaData */
        $sagaData = Stub::makeEmpty(SagaDataInterface::class);
        /** @var SagaActions|MockObject $actions */
        $actions = Stub::makeEmpty(SagaActions::class);
        /** @var SagaExecutionStateSerializerInterface|MockObject $executionStateSerializer */
        $executionStateSerializer = Stub::makeEmpty(SagaExecutionStateSerializerInterface::class);
        /** @var MessageIdGeneratorInterface|MockObject $messageIdGenerator */
        $messageIdGenerator = Stub::makeEmpty(MessageIdGeneratorInterface::class);
        /** @var SagaLockerInterface $sagaLocker */
        $sagaLocker = Stub::makeEmpty(SagaLockerInterface::class);
        /** @var ReplyMessageFactoryInterface $replyMessageFactory */
        $replyMessageFactory = Stub::makeEmpty(ReplyMessageFactoryInterface::class);

        $actions->expects(self::once())->method('getLocalException')->willReturn(null);
        $actions->expects(self::once())->method('getCommand')->willReturn(
            Stub::makeEmpty(RemoteCommandInterface::class)
        );
        $commandProducer->expects(self::once())->method('send');

        $processor = new SagaActionsProcessor(
            $instanceRepo,
            $sagaSerializer,
            $executionStateSerializer,
            $messageIdGenerator,
            $sagaLocker,
            $replyMessageFactory,
            $commandProducer
        );
        $processor->processActions($saga, $sagaInstance, $sagaData, $actions);
    }

    /**
     * @dataProvider lastActionsProvider
     * @covers       \PhpSagas\Orchestrator\ExecutionEngine\SagaActionsProcessor::processActions
     *
     * @param bool $isCompensating
     * @param string $sagaMethodOnce
     * @param string $sagaMethodNever
     * @param string $status
     *
     * @throws \Exception
     */
    public function testProcessLastActionsWorks(
        bool $isCompensating,
        string $sagaMethodOnce,
        string $sagaMethodNever,
        string $status
    ): void {
        /** @var SagaInstanceRepositoryInterface|MockObject $instanceRepo */
        $instanceRepo = Stub::makeEmpty(SagaInstanceRepositoryInterface::class);
        /** @var SagaSerializerInterface|MockObject $sagaSerializer */
        $sagaSerializer = Stub::makeEmpty(SagaSerializerInterface::class);
        /** @var SagaCommandProducerInterface|MockObject $commandProducer */
        $commandProducer = Stub::makeEmpty(SagaCommandProducerInterface::class);
        /** @var SagaInstanceInterface|MockObject $sagaInstance */
        $sagaInstance = Stub::makeEmpty(SagaInstanceInterface::class, ['getSagaId' => 'dummy id']);
        /** @var SagaDataInterface|MockObject $sagaData */
        $sagaData = Stub::makeEmpty(SagaDataInterface::class);
        /** @var SagaActions|MockObject $actions */
        $actions = Stub::makeEmpty(SagaActions::class);
        /** @var SagaInterface|MockObject $saga */
        $saga = Stub::makeEmpty(SagaInterface::class, ['getSagaDefinition' => Stub::makeEmpty(SagaDefinition::class, ['handleReply' => $actions])]);
        /** @var SagaExecutionStateSerializerInterface|MockObject $executionStateSerializer */
        $executionStateSerializer = Stub::makeEmpty(SagaExecutionStateSerializerInterface::class);
        /** @var MessageIdGeneratorInterface|MockObject $messageIdGenerator */
        $messageIdGenerator = Stub::makeEmpty(MessageIdGeneratorInterface::class);
        /** @var SagaLockerInterface|MockObject $sagaLocker */
        $sagaLocker = Stub::makeEmpty(SagaLockerInterface::class);
        /** @var ReplyMessageFactoryInterface $replyMessageFactory */
        $replyMessageFactory = Stub::makeEmpty(ReplyMessageFactoryInterface::class);

        $actions->expects(self::exactly(2))->method('isEndState')->willReturn(false, true);
        $sagaLocker->expects(self::once())->method('unlock');
        $actions->expects(self::once())->method('isCompensating')->willReturn($isCompensating);
        $saga->expects(self::once())->method($sagaMethodOnce);
        $saga->expects(self::never())->method($sagaMethodNever);
        $sagaInstance->expects(self::exactly(2))->method('setStatus')->withConsecutive([SagaStatusEnum::RUNNING], [$status]);

        $processor = new SagaActionsProcessor(
            $instanceRepo,
            $sagaSerializer,
            $executionStateSerializer,
            $messageIdGenerator,
            $sagaLocker,
            $replyMessageFactory,
            $commandProducer
        );
        $processor->processActions($saga, $sagaInstance, $sagaData, $actions);
    }

    /**
     * @return array
     */
    public function lastActionsProvider(): array
    {
        return [
            [false, 'onFinished', 'onFailed', SagaStatusEnum::FINISHED],
            [true, 'onFailed', 'onFinished', SagaStatusEnum::FAILED],
        ];
    }

    /**
     * @covers \PhpSagas\Orchestrator\ExecutionEngine\SagaActionsProcessor::processActions
     * @throws \Exception
     */
    public function testProcessLocalCommandActionsInfiniteLoopPrevented(): void
    {
        /** @var SagaInstanceRepositoryInterface|MockObject $instanceRepo */
        $instanceRepo = Stub::makeEmpty(SagaInstanceRepositoryInterface::class);
        /** @var SagaSerializerInterface|MockObject $sagaSerializer */
        $sagaSerializer = Stub::makeEmpty(SagaSerializerInterface::class);
        /** @var SagaCommandProducerInterface|MockObject $commandProducer */
        $commandProducer = Stub::makeEmpty(SagaCommandProducerInterface::class);
        /** @var SagaInterface|MockObject $saga */
        $saga = Stub::makeEmpty(
            SagaInterface::class,
            ['getSagaDefinition' => Stub::makeEmpty(
                SagaDefinition::class,
                ['handleReply' => Stub::makeEmpty(
                    SagaActions::class,
                    ['isLocal' => true]
                )]
            )]
        );
        /** @var SagaInstanceInterface|MockObject $sagaInstance */
        $sagaInstance = Stub::makeEmpty(SagaInstanceInterface::class, ['getSagaId' => 'dummy id']);
        /** @var SagaDataInterface|MockObject $sagaData */
        $sagaData = Stub::makeEmpty(SagaDataInterface::class);
        /** @var SagaActions|MockObject $actions */
        $actions = Stub::makeEmpty(SagaActions::class, ['isLocal' => true]);
        /** @var SagaExecutionStateSerializerInterface|MockObject $executionStateSerializer */
        $executionStateSerializer = Stub::makeEmpty(SagaExecutionStateSerializerInterface::class);
        /** @var MessageIdGeneratorInterface|MockObject $messageIdGenerator */
        $messageIdGenerator = Stub::makeEmpty(MessageIdGeneratorInterface::class);
        /** @var SagaLockerInterface $sagaLocker */
        $sagaLocker = Stub::makeEmpty(SagaLockerInterface::class);
        /** @var ReplyMessageFactoryInterface $replyMessageFactory */
        $replyMessageFactory = Stub::makeEmpty(ReplyMessageFactoryInterface::class);

        /** @see \PhpSagas\Orchestrator\ExecutionEngine\SagaActionsProcessor::MAX_CONSECUTIVE_LOCAL_COMMAND_ACTIONS */
        $expectedCounts = 10;
        $instanceRepo->expects(self::exactly($expectedCounts))->method('saveSaga');

        $processor = new SagaActionsProcessor(
            $instanceRepo,
            $sagaSerializer,
            $executionStateSerializer,
            $messageIdGenerator,
            $sagaLocker,
            $replyMessageFactory,
            $commandProducer
        );
        $processor->processActions($saga, $sagaInstance, $sagaData, $actions);
    }
}
