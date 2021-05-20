<?php

namespace PhpSagas\Orchestrator\Tests\_support\Implementation\TravelTourSaga\Command;

use PhpSagas\Orchestrator\BuildEngine\SagaDataInterface;
use PhpSagas\Orchestrator\Command\CommandDataInterface;
use PhpSagas\Orchestrator\Command\RemoteCommandInterface;
use PhpSagas\Orchestrator\Tests\_support\Implementation\CommandExecutionDetectorInterface;
use PhpSagas\Orchestrator\Tests\_support\Implementation\TravelTourSaga\BuyTourSagaData;
use PhpSagas\Orchestrator\Tests\_support\Implementation\TravelTourSaga\CommandData\ProcessTicketsBookingData;

/**
 * @author Oleg Filatov <phpsagas@gmail.com>
 */
class ConfirmTicketsBookingCommand implements RemoteCommandInterface
{
    public const COMMAND_TYPE = 'confirm_tickets_booking_command';

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
        return new ProcessTicketsBookingData(
            $sagaData->getDepartureTicketId(),
            $sagaData->getReturnTicketId()
        );
    }
}
