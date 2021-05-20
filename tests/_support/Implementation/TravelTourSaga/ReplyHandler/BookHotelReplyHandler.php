<?php

namespace PhpSagas\Orchestrator\Tests\_support\Implementation\TravelTourSaga\ReplyHandler;

use PhpSagas\Common\Message\ReplyMessage;
use PhpSagas\Orchestrator\BuildEngine\ReplyHandlerInterface;
use PhpSagas\Orchestrator\BuildEngine\SagaDataInterface;
use PhpSagas\Orchestrator\Tests\_support\Implementation\CommandExecutionDetectorInterface;
use PhpSagas\Orchestrator\Tests\_support\Implementation\TravelTourSaga\BuyTourSagaData;

/**
 * @author Oleg Filatov <phpsagas@gmail.com>
 */
class BookHotelReplyHandler implements ReplyHandlerInterface
{
    /** @var CommandExecutionDetectorInterface */
    private $executionDetector;

    public function __construct(CommandExecutionDetectorInterface $detector)
    {
        $this->executionDetector = $detector;
    }

    /**
     * @param ReplyMessage                      $message
     * @param SagaDataInterface|BuyTourSagaData $sagaData
     */
    public function handle(ReplyMessage $message, SagaDataInterface $sagaData): void
    {
        $this->executionDetector->execute();
        if ($message->isSuccess()) {
            $payload = json_decode($message->getPayload(), true);
            $sagaData->setHotelBookingId($payload['hotelBookingId']);
        }
    }
}
