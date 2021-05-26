<?php

namespace PhpSagas\Orchestrator\Tests\_support\Implementation;

use PhpSagas\Contracts\SagaDataInterface;
use PhpSagas\Orchestrator\BuildEngine\SagaInterface;
use PhpSagas\Orchestrator\InstantiationEngine\SagaFactoryInterface;
use PhpSagas\Orchestrator\Tests\_support\Implementation\TravelTourSaga\Command\BookHotelCommand;
use PhpSagas\Orchestrator\Tests\_support\Implementation\TravelTourSaga\ReplyHandler\BookHotelReplyHandler;
use PhpSagas\Orchestrator\Tests\_support\Implementation\TravelTourSaga\Command\BookTicketsCommand;
use PhpSagas\Orchestrator\Tests\_support\Implementation\TravelTourSaga\ReplyHandler\BookTicketsReplyHandler;
use PhpSagas\Orchestrator\Tests\_support\Implementation\TravelTourSaga\Command\BuyTourCommand;
use PhpSagas\Orchestrator\Tests\_support\Implementation\TravelTourSaga\BuyTourSaga;
use PhpSagas\Orchestrator\Tests\_support\Implementation\TravelTourSaga\Command\ConfirmHotelBookingCommand;
use PhpSagas\Orchestrator\Tests\_support\Implementation\TravelTourSaga\Command\ConfirmTicketsBookingCommand;
use PhpSagas\Orchestrator\Tests\_support\Implementation\TravelTourSaga\Command\ConfirmTourCommand;
use PhpSagas\Orchestrator\Tests\_support\Implementation\TravelTourSaga\Command\ObtainVisaCommand;
use PhpSagas\Orchestrator\Tests\_support\Implementation\TravelTourSaga\ReplyHandler\ObtainVisaReplyHandler;
use PhpSagas\Orchestrator\Tests\_support\Implementation\TravelTourSaga\Command\RejectHotelBookingCommand;
use PhpSagas\Orchestrator\Tests\_support\Implementation\TravelTourSaga\Command\RejectTicketsBookingCommand;
use PhpSagas\Orchestrator\Tests\_support\Implementation\TravelTourSaga\Command\RejectTourCommand;

/**
 * @author Oleg Filatov <phpsagas@gmail.com>
 */
class SagaFactory implements SagaFactoryInterface
{
    /** @var SagaExecutionDetectorInterface */
    private $sagaExecutionDetector;
    /** @var BuyTourCommand */
    private $buyTourCommand;
    /** @var RejectTourCommand */
    private $rejectTourCommand;
    /** @var \PhpSagas\Orchestrator\Tests\_support\Implementation\TravelTourSaga\Command\ConfirmTourCommand */
    private $confirmTourCommand;
    /** @var BookTicketsCommand */
    private $bookTicketsCommand;
    /** @var \PhpSagas\Orchestrator\Tests\_support\Implementation\TravelTourSaga\Command\ConfirmTicketsBookingCommand */
    private $confirmTicketsBookingCommand;
    /** @var RejectTicketsBookingCommand */
    private $rejectTicketsBookingCommand;
    /** @var BookTicketsReplyHandler */
    private $bookTicketsReplyHandler;
    /** @var BookHotelCommand */
    private $bookHotelCommand;
    /** @var ConfirmHotelBookingCommand */
    private $confirmHotelBookingCommand;
    /** @var RejectHotelBookingCommand */
    private $rejectHotelBookingCommand;
    /** @var \PhpSagas\Orchestrator\Tests\_support\Implementation\TravelTourSaga\ReplyHandler\BookHotelReplyHandler */
    private $bookHotelReplyHandler;
    /** @var ObtainVisaCommand */
    private $obtainVisaCommand;
    /** @var ObtainVisaReplyHandler */
    private $obtainVisaReplyHandler;

    public function __construct(
        SagaExecutionDetectorInterface $executionDetector,
        BuyTourCommand $buyTourCommand,
        RejectTourCommand $rejectTourCommand,
        ConfirmTourCommand $confirmTourCommand,
        BookTicketsCommand $bookTicketsCommand,
        ConfirmTicketsBookingCommand $confirmTicketsBookingCommand,
        RejectTicketsBookingCommand $rejectTicketsBookingCommand,
        BookTicketsReplyHandler $bookTicketsReplyHandler,
        BookHotelCommand $bookHotelCommand,
        ConfirmHotelBookingCommand $confirmHotelBookingCommand,
        RejectHotelBookingCommand $rejectHotelBookingCommand,
        BookHotelReplyHandler $bookHotelReplyHandler,
        ObtainVisaCommand $obtainVisaCommand,
        ObtainVisaReplyHandler $obtainVisaReplyHandler
    ) {
        $this->sagaExecutionDetector = $executionDetector;
        $this->buyTourCommand = $buyTourCommand;
        $this->rejectTourCommand = $rejectTourCommand;
        $this->confirmTourCommand = $confirmTourCommand;
        $this->bookTicketsCommand = $bookTicketsCommand;
        $this->confirmTicketsBookingCommand = $confirmTicketsBookingCommand;
        $this->rejectTicketsBookingCommand = $rejectTicketsBookingCommand;
        $this->bookTicketsReplyHandler = $bookTicketsReplyHandler;
        $this->bookHotelCommand = $bookHotelCommand;
        $this->confirmHotelBookingCommand = $confirmHotelBookingCommand;
        $this->rejectHotelBookingCommand = $rejectHotelBookingCommand;
        $this->bookHotelReplyHandler = $bookHotelReplyHandler;
        $this->obtainVisaCommand = $obtainVisaCommand;
        $this->obtainVisaReplyHandler = $obtainVisaReplyHandler;
    }


    public function create(string $sagaType, SagaDataInterface $sagaInitialData = null): SagaInterface
    {
        switch ($sagaType) {
            case BuyTourSaga::SAGA_TYPE:
                return new BuyTourSaga(
                    $this->sagaExecutionDetector,
                    $this->buyTourCommand,
                    $this->rejectTourCommand,
                    $this->confirmTourCommand,
                    $this->bookTicketsCommand,
                    $this->confirmTicketsBookingCommand,
                    $this->rejectTicketsBookingCommand,
                    $this->bookTicketsReplyHandler,
                    $this->bookHotelCommand,
                    $this->confirmHotelBookingCommand,
                    $this->rejectHotelBookingCommand,
                    $this->bookHotelReplyHandler,
                    $this->obtainVisaCommand,
                    $this->obtainVisaReplyHandler
                );
            default:
                throw new \UnexpectedValueException('Unknown saga type: ' . $sagaType);
        }
    }
}
