<?php

namespace PhpSagas\Orchestrator\Tests\_support\Implementation\TravelTourSaga\CommandData;

use PhpSagas\Orchestrator\Command\CommandDataInterface;

/**
 * @author Oleg Filatov <phpsagas@gmail.com>
 */
class ProcessTicketsBookingData implements CommandDataInterface, \JsonSerializable
{
    /** @var string */
    private $departureTicketId;
    /** @var string */
    private $returnTicketId;

    public function __construct(string $departureTicketId, string $returnTicketId)
    {
        $this->departureTicketId = $departureTicketId;
        $this->returnTicketId = $returnTicketId;
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
            'departureTicketId' => $this->departureTicketId,
            'returnTicketId' => $this->returnTicketId,
        ];
    }
}
