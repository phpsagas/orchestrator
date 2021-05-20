<?php

namespace PhpSagas\Orchestrator\Tests;

use Codeception\Stub;
use Codeception\Test\Unit;
use PHPUnit\Framework\MockObject\MockObject;
use PhpSagas\Orchestrator\BuildEngine\LocalStep;
use PhpSagas\Orchestrator\BuildEngine\SagaActions;
use PhpSagas\Orchestrator\BuildEngine\SagaDataInterface;
use PhpSagas\Orchestrator\Command\LocalCommandInterface;

/**
 * @covers \PhpSagas\Orchestrator\BuildEngine\LocalStep
 *
 * @author Oleg Filatov <phpsagas@gmail.com>
 */
class LocalStepTest extends Unit
{
    /**
     * @covers \PhpSagas\Orchestrator\BuildEngine\LocalStep::execute
     * @throws \Exception
     */
    public function testExceptionThrownWhenSagaDataTypeUnknown()
    {
        /** @var SagaDataInterface $sagaData */
        $sagaData = Stub::makeEmpty(SagaDataInterface::class);
        /** @var LocalCommandInterface $command */
        $command = Stub::makeEmpty(LocalCommandInterface::class, ['getSagaDataType' => 'not_equal_' . get_class($sagaData)]);
        /** @var SagaActions|MockObject $actions */
        $actions = Stub::makeEmpty(SagaActions::class);

        $actions->expects(self::never())->method('setLocal');

        $step = new LocalStep($command, null);
        $this->expectException(\UnexpectedValueException::class);
        $step->execute($sagaData, false, $actions);
    }
}
