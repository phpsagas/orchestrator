<?php

namespace PhpSagas\Orchestrator\Tests\_support\Implementation;

use PhpSagas\Contracts\CommandDataInterface;
use PhpSagas\Contracts\MessagePayloadSerializerInterface;

/**
 * @author Oleg Filatov <phpsagas@gmail.com>
 */
class JsonEncodeMessagePayloadSerializer implements MessagePayloadSerializerInterface
{
    public function serialize(CommandDataInterface $commandData): string
    {
        return json_encode($commandData);
    }
}
