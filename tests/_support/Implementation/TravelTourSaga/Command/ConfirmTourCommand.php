<?php

namespace PhpSagas\Orchestrator\Tests\_support\Implementation\TravelTourSaga\Command;

use PhpSagas\Contracts\SagaDataInterface;
use PhpSagas\Orchestrator\Command\LocalCommandInterface;
use PhpSagas\Orchestrator\Tests\_support\Implementation\CommandExecutionDetectorInterface;
use PhpSagas\Orchestrator\Tests\_support\Implementation\TravelTourSaga\BuyTourSagaData;
use PhpSagas\Orchestrator\Tests\_support\Implementation\TravelTourSaga\TouristAgencyService\TourService;

/**
 * @author Oleg Filatov <phpsagas@gmail.com>
 */
class ConfirmTourCommand implements LocalCommandInterface
{
    /** @var TourService */
    private $tourService;
    /** @var CommandExecutionDetectorInterface */
    private $executionDetector;

    public function __construct(TourService $tourService, CommandExecutionDetectorInterface $detector)
    {
        $this->tourService = $tourService;
        $this->executionDetector = $detector;
    }

    /**
     * @param SagaDataInterface|BuyTourSagaData $sagaData
     */
    public function execute(SagaDataInterface $sagaData): void
    {
        $this->executionDetector->execute();
        $this->tourService->confirmTour(
            $sagaData->getTourId(),
            $sagaData->getDepartureTicketId(),
            $sagaData->getReturnTicketId(),
            $sagaData->getHotelBookingId(),
            $sagaData->getVisaId()
        );
    }

    public function getSagaDataType(): string
    {
        return BuyTourSagaData::class;
    }
}
