<?php

namespace PhpSagas\Orchestrator\Tests\_support\Implementation\TravelTourSaga\CommandData;

use PhpSagas\Contracts\CommandDataInterface;

/**
 * @author Oleg Filatov <phpsagas@gmail.com>
 */
class ObtainVisaData implements CommandDataInterface, \JsonSerializable
{
    /** @var string */
    private $hotelBookingId;
    /** @var string */
    private $departureTicketId;
    /** @var string */
    private $returnTicketId;

    public function __construct(string $hotelBookingId, string $departureTicketId, string $returnTicketId)
    {
        $this->hotelBookingId = $hotelBookingId;
        $this->departureTicketId = $departureTicketId;
        $this->returnTicketId = $returnTicketId;
    }

    /**
     * @return string
     */
    public function getHotelBookingId(): string
    {
        return $this->hotelBookingId;
    }

    /**
     * @return string
     */
    public function getDepartureTicketId(): string
    {
        return $this->departureTicketId;
    }

    /**
     * @return string
     */
    public function getReturnTicketId(): string
    {
        return $this->returnTicketId;
    }

    public function jsonSerialize()
    {
        return [
            'hotelBookingId'    => $this->hotelBookingId,
            'departureTicketId' => $this->departureTicketId,
            'returnTicketId'    => $this->returnTicketId,
        ];
    }
}
