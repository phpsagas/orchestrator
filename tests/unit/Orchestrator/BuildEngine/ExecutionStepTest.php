<?php

namespace PhpSagas\Orchestrator\Tests;

use Codeception\Stub;
use Codeception\Test\Unit;
use PHPUnit\Framework\MockObject\MockObject;
use PhpSagas\Contracts\SagaDataInterface;
use PhpSagas\Orchestrator\BuildEngine\SagaExecutionState;
use PhpSagas\Orchestrator\BuildEngine\SagaStepInterface;
use PhpSagas\Orchestrator\BuildEngine\ExecutionStep;

/**
 * @covers \PhpSagas\Orchestrator\BuildEngine\ExecutionStep
 *
 * @author Oleg Filatov <phpsagas@gmail.com>
 */
class ExecutionStepTest extends Unit
{
    /**
     * @dataProvider sizeProvider
     * @covers       \PhpSagas\Orchestrator\BuildEngine\ExecutionStep::stepIndex
     *
     * @param $sagaStep
     * @param $skipped
     * @param $expectedSize
     */
    public function testSizeWithStepWorks(?SagaStepInterface $sagaStep, int $skipped, int $expectedSize): void
    {
        $step = new ExecutionStep($sagaStep, $skipped, false);

        $size = $step->stepIndex();

        self::assertEquals($expectedSize, $size);
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function sizeProvider(): array
    {
        return [
            [Stub::makeEmpty(SagaStepInterface::class), 0, 1],
            [null, 0, 0],
            [Stub::makeEmpty(SagaStepInterface::class), 3, 4],
        ];
    }

    /**
     * @dataProvider isEmptyProvider
     * @covers       \PhpSagas\Orchestrator\BuildEngine\ExecutionStep::isEmpty
     *
     * @param SagaStepInterface|null $sagaStep
     * @param bool                   $expected
     */
    public function testIsEmptyWorks(?SagaStepInterface $sagaStep, bool $expected): void
    {
        $step = new ExecutionStep($sagaStep, 0, false);

        $isEmpty = $step->isEmpty();

        $expected ? self::assertTrue($isEmpty) : self::assertFalse($isEmpty);
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function isEmptyProvider(): array
    {
        return [
            [Stub::makeEmpty(SagaStepInterface::class), false],
            [null, true],
        ];
    }

    /**
     * @covers \PhpSagas\Orchestrator\BuildEngine\ExecutionStep::execute
     * @throws \Exception
     */
    public function testExecuteStepWorks(): void
    {
        $isCompensating = false;
        /** @var SagaDataInterface $sagaData */
        $sagaData = Stub::makeEmpty(SagaDataInterface::class);
        /** @var SagaExecutionState|MockObject $executionState */
        $executionState = Stub::makeEmpty(SagaExecutionState::class);
        /** @var SagaStepInterface|MockObject $sagaStep */
        $sagaStep = Stub::makeEmpty(SagaStepInterface::class);

        $sagaStep->expects(self::once())->method('execute')->with($sagaData, $isCompensating);

        $executionState->expects(self::once())->method('nextState')->willReturnSelf();
        $executionState->expects(self::once())->method('isCompensating');
        $executionState->expects(self::once())->method('toJson');

        $step = new ExecutionStep($sagaStep, 0, $isCompensating);

        $step->execute($sagaData, $executionState);
    }
}
