<?php

namespace PhpSagas\Orchestrator\Tests\_support\Implementation\TravelTourSaga\ReplyHandler;

use PhpSagas\Contracts\ReplyMessageInterface;
use PhpSagas\Orchestrator\BuildEngine\ReplyHandlerInterface;
use PhpSagas\Contracts\SagaDataInterface;
use PhpSagas\Orchestrator\Tests\_support\Implementation\CommandExecutionDetectorInterface;
use PhpSagas\Orchestrator\Tests\_support\Implementation\TravelTourSaga\BuyTourSagaData;

/**
 * @author Oleg Filatov <phpsagas@gmail.com>
 */
class BookTicketsReplyHandler implements ReplyHandlerInterface
{
    /** @var CommandExecutionDetectorInterface */
    private $executionDetector;

    public function __construct(CommandExecutionDetectorInterface $detector)
    {
        $this->executionDetector = $detector;
    }

    /**
     * @param ReplyMessageInterface             $message
     * @param SagaDataInterface|BuyTourSagaData $sagaData
     */
    public function handle(ReplyMessageInterface $message, SagaDataInterface $sagaData): void
    {
        $this->executionDetector->execute();
        if ($message->isSuccess()) {
            $payload = json_decode($message->getPayload(), true);
            $sagaData->setDepartureTicketId($payload['departureTicketId']);
            $sagaData->setReturnTicketId($payload['returnTicketId']);
        }
    }
}
