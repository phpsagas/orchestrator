<?php

namespace PhpSagas\Orchestrator\Tests\_support\Implementation;

use PhpSagas\Common\Message\CommandMessage;
use PhpSagas\Orchestrator\ExecutionEngine\MessageProducerInterface;
use PhpSagas\Orchestrator\Tests\_support\Implementation\TravelTourSaga\Command\BookHotelCommand;
use PhpSagas\Orchestrator\Tests\_support\Implementation\TravelTourSaga\Command\BookTicketsCommand;
use PhpSagas\Orchestrator\Tests\_support\Implementation\TravelTourSaga\Command\ConfirmHotelBookingCommand;
use PhpSagas\Orchestrator\Tests\_support\Implementation\TravelTourSaga\Command\ConfirmTicketsBookingCommand;
use PhpSagas\Orchestrator\Tests\_support\Implementation\TravelTourSaga\FlightTicketService\TicketsBookingConsumer;
use PhpSagas\Orchestrator\Tests\_support\Implementation\TravelTourSaga\HotelService\HotelBookingConsumer;
use PhpSagas\Orchestrator\Tests\_support\Implementation\TravelTourSaga\Command\ObtainVisaCommand;
use PhpSagas\Orchestrator\Tests\_support\Implementation\TravelTourSaga\Command\RejectHotelBookingCommand;
use PhpSagas\Orchestrator\Tests\_support\Implementation\TravelTourSaga\Command\RejectTicketsBookingCommand;
use PhpSagas\Orchestrator\Tests\_support\Implementation\TravelTourSaga\VisaCenterService\VisaObtainingConsumer;

/**
 * @author Oleg Filatov <phpsagas@gmail.com>
 */
class InMemoryMessageProducer implements MessageProducerInterface
{
    /** @var HotelBookingConsumer */
    private $hotelConsumer;
    /** @var TicketsBookingConsumer */
    private $ticketsConsumer;
    /** @var VisaObtainingConsumer */
    private $visaConsumer;

    public function __construct(
        HotelBookingConsumer $hotelConsumer,
        TicketsBookingConsumer $ticketsConsumer,
        VisaObtainingConsumer $visaConsumer
    ) {
        $this->hotelConsumer = $hotelConsumer;
        $this->ticketsConsumer = $ticketsConsumer;
        $this->visaConsumer = $visaConsumer;
    }

    public function send(CommandMessage $message): void
    {
        switch ($message->getCommandType()) {
            case BookTicketsCommand::COMMAND_TYPE:
                $this->ticketsConsumer->handleTicketsBooked($message);
                break;
            case RejectTicketsBookingCommand::COMMAND_TYPE:
                $this->ticketsConsumer->handleBookingRejected($message);
                break;
            case ConfirmTicketsBookingCommand::COMMAND_TYPE:
                $this->ticketsConsumer->handleBookingConfirmed($message);
                break;
            case BookHotelCommand::COMMAND_TYPE:
                $this->hotelConsumer->handleHotelBooked($message);
                break;
            case RejectHotelBookingCommand::COMMAND_TYPE:
                $this->hotelConsumer->handleBookingRejected($message);
                break;
            case ConfirmHotelBookingCommand::COMMAND_TYPE:
                $this->hotelConsumer->handleBookingConfirmed($message);
                break;
            case ObtainVisaCommand::COMMAND_TYPE:
                $this->visaConsumer->handleVisaObtained($message);
                break;
        }
    }
}
