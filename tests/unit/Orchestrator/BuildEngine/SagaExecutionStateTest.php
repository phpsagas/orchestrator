<?php

namespace PhpSagas\Orchestrator\Tests;

use Codeception\Test\Unit;
use PhpSagas\Orchestrator\BuildEngine\SagaExecutionState;

/**
 * @covers \PhpSagas\Orchestrator\BuildEngine\SagaExecutionState
 *
 * @author Oleg Filatov <phpsagas@gmail.com>
 */
class SagaExecutionStateTest extends Unit
{
    /**
     * @covers \PhpSagas\Orchestrator\BuildEngine\SagaExecutionState::startCompensating
     */
    public function testStartCompensatingWorks(): void
    {
        $executionState = SagaExecutionState::start();
        $executionState = $executionState->nextState(1);

        $result = $executionState->startCompensating();

        self::assertEquals(0, $result->getCurrentStepIndex());
        self::assertTrue($result->isCompensating());
    }

    /**
     * @covers \PhpSagas\Orchestrator\BuildEngine\SagaExecutionState::nextState
     */
    public function testNextStateNoCompensatingWorks(): void
    {
        $state = SagaExecutionState::start();

        $result = $state->nextState(3);

        self::assertEquals(2, $result->getCurrentStepIndex());
        self::assertFalse($result->isCompensating());
    }

    /**
     * @covers \PhpSagas\Orchestrator\BuildEngine\SagaExecutionState::finish
     */
    public function testMakeEndStateWorks(): void
    {
        $state = SagaExecutionState::finish();

        self::assertNull($state->getCurrentStepIndex());
        self::assertFalse($state->isCompensating());
    }

    /**
     * @covers \PhpSagas\Orchestrator\BuildEngine\SagaExecutionState::toJson
     */
    public function testSerializeToJsonWorks(): void
    {
        $state = SagaExecutionState::start();
        $state = $state->nextState(2);
        $state = $state->startCompensating();

        $json = $state->toJson();

        self::assertJson($json);
        self::assertJsonStringEqualsJsonString(
            '{"currentStepIndex": 1, "isCompensating": true}',
            $json
        );
    }

    /**
     * @covers \PhpSagas\Orchestrator\BuildEngine\SagaExecutionState::fromJson
     */
    public function testDeserializeFromJsonWorks(): void
    {
        $json = '{"currentStepIndex": 5, "isCompensating": false}';

        $state = SagaExecutionState::fromJson($json);

        self::assertEquals(5, $state->getCurrentStepIndex());
        self::assertFalse($state->isCompensating());
    }

    /**
     * @covers \PhpSagas\Orchestrator\BuildEngine\SagaExecutionState::fromJson
     */
    public function testDeserializeInvalidJsonFailed(): void
    {
        $invalidJson = '';

        $this->expectException(\InvalidArgumentException::class);

        SagaExecutionState::fromJson($invalidJson);
    }
}
