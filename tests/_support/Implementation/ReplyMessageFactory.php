<?php

namespace PhpSagas\Orchestrator\Tests\_support\Implementation;

use Codeception\Util\Stub;
use PhpSagas\Contracts\ReplyMessageFactoryInterface;
use PhpSagas\Contracts\ReplyMessageInterface;

class ReplyMessageFactory implements ReplyMessageFactoryInterface
{
    /**
     * @param string $sagaId
     * @param string $correlationId
     * @param string $payload
     *
     * @return ReplyMessageInterface
     * @throws \Exception
     */
    public function makeSuccess(string $sagaId, string $correlationId, string $payload): ReplyMessageInterface
    {
        /** @var ReplyMessageInterface $message */
        $message = Stub::makeEmpty(
            ReplyMessageInterface::class,
            [
                'getSagaId' => $sagaId,
                'getPayload' => $payload,
                'getCorrelationId' => $correlationId,
                'isSuccess' => true
            ]
        );
        return $message;
    }

    /**
     * @param string $sagaId
     * @param string $correlationId
     * @param string $payload
     *
     * @return ReplyMessageInterface
     * @throws \Exception
     */
    public function makeFailure(string $sagaId, string $correlationId, string $payload): ReplyMessageInterface
    {
        /** @var ReplyMessageInterface $message */
        $message = Stub::makeEmpty(
            ReplyMessageInterface::class,
            [
                'getSagaId' => $sagaId,
                'getPayload' => $payload,
                'getCorrelationId' => $correlationId,
                'isSuccess' => false
            ]
        );
        return $message;
    }
}
