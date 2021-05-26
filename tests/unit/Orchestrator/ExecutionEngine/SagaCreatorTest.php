<?php

namespace PhpSagas\Orchestrator\Tests;

use Codeception\Test\Unit;
use Codeception\Util\Stub;
use PHPUnit\Framework\MockObject\MockObject;
use PhpSagas\Orchestrator\BuildEngine\SagaActions;
use PhpSagas\Contracts\SagaDataInterface;
use PhpSagas\Orchestrator\BuildEngine\SagaDefinition;
use PhpSagas\Orchestrator\BuildEngine\SagaInterface;
use PhpSagas\Orchestrator\Command\LocalCommandException;
use PhpSagas\Orchestrator\ExecutionEngine\SagaActionsProcessor;
use PhpSagas\Orchestrator\ExecutionEngine\SagaCreator;
use PhpSagas\Orchestrator\ExecutionEngine\SagaInstanceRepositoryInterface;
use PhpSagas\Orchestrator\ExecutionEngine\SagaLockerInterface;
use PhpSagas\Contracts\SagaSerializerInterface;
use PhpSagas\Orchestrator\InstantiationEngine\SagaInstanceFactoryInterface;

/**
 * @covers \PhpSagas\Orchestrator\ExecutionEngine\SagaCreator
 *
 * @author Oleg Filatov <phpsagas@gmail.com>
 */
class SagaCreatorTest extends Unit
{
    /**
     * @covers \PhpSagas\Orchestrator\ExecutionEngine\SagaCreator::create
     * @throws \Exception
     */
    public function testCreateSagaInstanceWorks(): void
    {
        /** @var SagaInstanceRepositoryInterface|MockObject $sagaInstanceRepo */
        $sagaInstanceRepo = Stub::makeEmpty(SagaInstanceRepositoryInterface::class);
        /** @var SagaSerializerInterface|MockObject $sagaSerializer */
        $sagaSerializer = Stub::makeEmpty(SagaSerializerInterface::class);
        /** @var SagaInstanceFactoryInterface|MockObject $sagaInstanceFactory */
        $sagaInstanceFactory = Stub::makeEmpty(SagaInstanceFactoryInterface::class);
        /** @var SagaActionsProcessor|MockObject $sagaActionsProcessor */
        $sagaActionsProcessor = Stub::makeEmpty(SagaActionsProcessor::class);
        /** @var SagaInterface|MockObject $saga */
        $saga = Stub::makeEmpty(SagaInterface::class);
        /** @var SagaDataInterface|MockObject $sagaData */
        $sagaData = Stub::makeEmpty(SagaDataInterface::class);
        /** @var SagaLockerInterface|MockObject $sagaLocker */
        $sagaLocker = Stub::makeEmpty(SagaLockerInterface::class);

        $sagaInstanceFactory->expects(self::once())->method('makeSagaInstance');
        $sagaInstanceRepo->expects(self::once())->method('saveSaga');
        $saga->expects(self::once())->method('onStarted');
        $sagaActionsProcessor->expects(self::once())->method('processActions');
        $sagaLocker->expects(self::once())->method('lock');

        $creator = new SagaCreator($sagaInstanceRepo, $sagaInstanceFactory, $sagaLocker, $sagaActionsProcessor);

        $creator->create($saga, $sagaData);
    }

    /**
     * @covers \PhpSagas\Orchestrator\ExecutionEngine\SagaCreator::create
     * @throws \Exception
     */
    public function testCreateSagaInstanceThrowsExceptionIfFirstActionFailed(): void
    {
        /** @var SagaInstanceRepositoryInterface|MockObject $sagaInstanceRepo */
        $sagaInstanceRepo = Stub::makeEmpty(SagaInstanceRepositoryInterface::class);
        /** @var SagaInstanceFactoryInterface|MockObject $sagaInstanceFactory */
        $sagaInstanceFactory = Stub::makeEmpty(SagaInstanceFactoryInterface::class);
        /** @var SagaActionsProcessor|MockObject $sagaActionsProcessor */
        $sagaActionsProcessor = Stub::makeEmpty(SagaActionsProcessor::class);
        /** @var SagaDataInterface|MockObject $sagaData */
        $sagaData = Stub::makeEmpty(SagaDataInterface::class);
        /** @var SagaInterface|MockObject $saga */
        $saga = Stub::makeEmpty(
            SagaInterface::class,
            ['getSagaDefinition' => Stub::makeEmpty(
                SagaDefinition::class,
                ['start' => Stub::makeEmpty(
                    SagaActions::class,
                    ['getLocalException' => new LocalCommandException()]
                )]
            )]
        );
        /** @var SagaLockerInterface|MockObject $sagaLocker */
        $sagaLocker = Stub::makeEmpty(SagaLockerInterface::class);

        $creator = new SagaCreator($sagaInstanceRepo, $sagaInstanceFactory, $sagaLocker, $sagaActionsProcessor);

        $this->expectException(LocalCommandException::class);
        $creator->create($saga, $sagaData);
    }
}
