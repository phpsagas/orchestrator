<?php

namespace PhpSagas\Orchestrator\Tests\_support\Implementation\TravelTourSaga\CommandData;

use PhpSagas\Contracts\CommandDataInterface;

/**
 * @author Oleg Filatov <phpsagas@gmail.com>
 */
class ProcessHotelBookingData implements CommandDataInterface, \JsonSerializable
{
    /** @var string */
    private $bookingId;

    public function __construct(string $bookingId)
    {
        $this->bookingId = $bookingId;
    }

    /**
     * @return string
     */
    public function getBookingId(): string
    {
        return $this->bookingId;
    }

    public function jsonSerialize()
    {
        return [
            'bookingId' => $this->bookingId,
        ];
    }
}
