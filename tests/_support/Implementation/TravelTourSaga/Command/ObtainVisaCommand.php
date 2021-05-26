<?php

namespace PhpSagas\Orchestrator\Tests\_support\Implementation\TravelTourSaga\Command;

use PhpSagas\Contracts\SagaDataInterface;
use PhpSagas\Contracts\CommandDataInterface;
use PhpSagas\Orchestrator\Command\RemoteCommandInterface;
use PhpSagas\Orchestrator\Tests\_support\Implementation\CommandExecutionDetectorInterface;
use PhpSagas\Orchestrator\Tests\_support\Implementation\TravelTourSaga\BuyTourSagaData;
use PhpSagas\Orchestrator\Tests\_support\Implementation\TravelTourSaga\CommandData\ObtainVisaData;

/**
 * @author Oleg Filatov <phpsagas@gmail.com>
 */
class ObtainVisaCommand implements RemoteCommandInterface
{
    public const COMMAND_TYPE = 'obtain_visa_command';

    /** @var CommandExecutionDetectorInterface */
    private $executionDetector;

    public function __construct(CommandExecutionDetectorInterface $executionDetector)
    {
        $this->executionDetector = $executionDetector;
    }

    public function getCommandType(): string
    {
        return self::COMMAND_TYPE;
    }

    public function getSagaDataClassName(): string
    {
        return BuyTourSagaData::class;
    }

    /**
     * @param SagaDataInterface|BuyTourSagaData $sagaData
     *
     * @return CommandDataInterface
     */
    public function getCommandData(SagaDataInterface $sagaData): CommandDataInterface
    {
        $this->executionDetector->execute();
        return new ObtainVisaData(
            $sagaData->getHotelBookingId(),
            $sagaData->getDepartureTicketId(),
            $sagaData->getReturnTicketId()
        );
    }

}
