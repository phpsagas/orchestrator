<?php

namespace PhpSagas\Orchestrator\Tests\_support\Implementation\TravelTourSaga\TouristAgencyService;

/**
 * @author Oleg Filatov <phpsagas@gmail.com>
 */
class TourService
{
    /** @var InMemoryTourRepository */
    private $repo;

    public function __construct(InMemoryTourRepository $repo)
    {
        $this->repo = $repo;
    }

    public function buyTour(
        string $country,
        string $city,
        \DateTimeImmutable $dateFrom,
        \DateTimeImmutable $dateTill
    ): Tour {
        $tour = Tour::createPending($country, $city, $dateFrom, $dateTill);
        $this->repo->save($tour);

        return $tour;
    }

    public function cancelTour(string $tourId): void
    {
        $tour = $this->repo->find($tourId);
        $tour->reject();
        $this->repo->save($tour);
    }

    public function confirmTour(
        string $tourId,
        string $departureTicketId,
        string $returnTicketId,
        string $bookingId,
        string $visaId
    ): void {
        $tour = $this->repo->find($tourId);
        $tour
            ->setHotelBookingId($bookingId)
            ->setVisaId($visaId)
            ->setDepartureTicketId($departureTicketId)
            ->setReturnedTicketId($returnTicketId);

        $tour->confirm();
        $this->repo->save($tour);
    }

    public function addTickets(string $tourId, string $departureTicketId, string $returnTicketId): void
    {
        $tour = $this->repo->find($tourId);
        $tour
            ->setDepartureTicketId($departureTicketId)
            ->setReturnedTicketId($returnTicketId)
        ;
        $this->repo->save($tour);
    }

    public function addBooking(string $tourId, string $bookingId): void
    {
        $tour = $this->repo->find($tourId);
        $tour->setHotelBookingId($bookingId);
        $this->repo->save($tour);
    }

    public function addVisa(string $tourId, string $visaId): void
    {
        $tour = $this->repo->find($tourId);
        $tour->setVisaId($visaId);
        $this->repo->save($tour);
    }
}
