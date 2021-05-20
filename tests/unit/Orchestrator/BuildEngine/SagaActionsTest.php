<?php

namespace PhpSagas\Orchestrator\Tests;

use Codeception\Test\Unit;
use PhpSagas\Orchestrator\BuildEngine\SagaActions;

/**
 * @covers \PhpSagas\Orchestrator\BuildEngine\SagaActions
 *
 * @author Oleg Filatov <phpsagas@gmail.com>
 */
class SagaActionsTest extends Unit
{
    /**
     * @covers \PhpSagas\Orchestrator\BuildEngine\SagaActions::getCommand
     */
    public function testLogicExceptionThrownWhenExpectedCommandNotSet()
    {
        $actions = new SagaActions(null, false);
        $this->expectException(\LogicException::class);
        $actions->getCommand();
    }

    /**
     * @covers \PhpSagas\Orchestrator\BuildEngine\SagaActions::getUpdatedSagaDataOrFail
     */
    public function testExceptionThrownWhenExpectedSagaDataNotSet()
    {
        $actions = new SagaActions(null, false);
        $this->expectException(\RuntimeException::class);
        $actions->getUpdatedSagaDataOrFail();
    }

    /**
     * @covers \PhpSagas\Orchestrator\BuildEngine\SagaActions::getExecutionStateOrFail
     */
    public function testExceptionThrownWhenExpectedExecutionStateNotSet()
    {
        $actions = new SagaActions(null, false);
        $this->expectException(\RuntimeException::class);
        $actions->getExecutionStateOrFail();
    }
}
