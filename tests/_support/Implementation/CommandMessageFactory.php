<?php

namespace PhpSagas\Orchestrator\Tests\_support\Implementation;

use Codeception\Util\Stub;
use PhpSagas\Contracts\CommandMessageFactoryInterface;
use PhpSagas\Contracts\CommandMessageInterface;

class CommandMessageFactory implements CommandMessageFactoryInterface
{
    /**
     * @param string $messageId
     * @param string $sagaId
     * @param string $sagaType
     * @param string $commandType
     * @param string $payload
     *
     * @return CommandMessageInterface
     * @throws \Exception
     */
    public function createCommandMessage(
        string $messageId,
        string $sagaId,
        string $sagaType,
        string $commandType,
        string $payload
    ): CommandMessageInterface {
        /** @var CommandMessageInterface $message */
        $message = Stub::makeEmpty(CommandMessageInterface::class, [
            'getId' => $messageId,
            'getSagaId' => $sagaId,
            'getSagaType' => $sagaType,
            'getCommandType' => $commandType,
            'getPayload' => $payload,
        ]);
        return $message;
    }
}
