<?php

namespace PhpSagas\Orchestrator\Tests\_support\Implementation;

use PhpSagas\Orchestrator\ExecutionEngine\SagaInstanceRepositoryInterface;
use PhpSagas\Orchestrator\InstantiationEngine\SagaInstanceInterface;

/**
 * @author Oleg Filatov <phpsagas@gmail.com>
 */
class InMemorySagaInstanceRepository implements SagaInstanceRepositoryInterface
{
    /** @var SagaInstanceInterface[] */
    private $sagas;
    /** @var int */
    private $nextId = 1;

    public function saveSaga(SagaInstanceInterface $sagaInstance): string
    {
        if (is_null($sagaInstance->getSagaId())) {
            $sagaInstance->setSagaId((string)$this->nextId++);
            $this->sagas[$sagaInstance->getSagaId()] = $sagaInstance;
        } else {
            $this->sagas[$sagaInstance->getSagaId()] = $sagaInstance;
        }

        return $sagaInstance->getSagaId();
    }

    public function findSaga(string $sagaId): SagaInstanceInterface
    {
        if (isset($this->sagas[$sagaId])) {
            return $this->sagas[$sagaId];
        }

        throw new \InvalidArgumentException('Saga not found: ' . $sagaId);
    }

    public function reset(): void
    {
        $this->sagas = [];
        $this->nextId = 1;
    }
}
