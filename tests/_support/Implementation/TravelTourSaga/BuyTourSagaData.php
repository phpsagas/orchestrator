<?php

namespace PhpSagas\Orchestrator\Tests\_support\Implementation\TravelTourSaga;

use PhpSagas\Orchestrator\BuildEngine\SagaDataInterface;

/**
 * @author Oleg Filatov <phpsagas@gmail.com>
 */
class BuyTourSagaData implements SagaDataInterface, \JsonSerializable
{
    /** @var string */
    private $country;
    /** @var string */
    private $city;
    /** @var \DateTimeImmutable */
    private $dateFrom;
    /** @var \DateTimeImmutable */
    private $dateTill;
    /** @var string|null */
    private $departureTicketId;
    /** @var string|null */
    private $returnTicketId;
    /** @var string|null */
    private $hotelBookingId;
    /** @var string|null */
    private $visaId;
    /** @var string|null */
    private $tourId;

    public function __construct(
        string $country,
        string $city,
        \DateTimeImmutable $dateFrom,
        \DateTimeImmutable $dateTill
    ) {
        $this->country = $country;
        $this->city = $city;
        $this->dateFrom = $dateFrom;
        $this->dateTill = $dateTill;
    }

    /**
     * @return string
     */
    public function getCountry(): string
    {
        return $this->country;
    }

    /**
     * @return string
     */
    public function getCity(): string
    {
        return $this->city;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getDateFrom(): \DateTimeImmutable
    {
        return $this->dateFrom;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getDateTill(): \DateTimeImmutable
    {
        return $this->dateTill;
    }

    /**
     * @return string|null
     */
    public function getDepartureTicketId(): ?string
    {
        return $this->departureTicketId;
    }

    /**
     * @param string|null $departureTicketId
     *
     * @return self
     */
    public function setDepartureTicketId(?string $departureTicketId): self
    {
        $this->departureTicketId = $departureTicketId;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getReturnTicketId(): ?string
    {
        return $this->returnTicketId;
    }

    /**
     * @param string|null $returnTicketId
     *
     * @return self
     */
    public function setReturnTicketId(?string $returnTicketId): self
    {
        $this->returnTicketId = $returnTicketId;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getHotelBookingId(): ?string
    {
        return $this->hotelBookingId;
    }

    /**
     * @param string|null $hotelBookingId
     *
     * @return self
     */
    public function setHotelBookingId(?string $hotelBookingId): self
    {
        $this->hotelBookingId = $hotelBookingId;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getVisaId(): ?string
    {
        return $this->visaId;
    }

    /**
     * @param string|null $visaId
     *
     * @return self
     */
    public function setVisaId(?string $visaId): self
    {
        $this->visaId = $visaId;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getTourId(): ?string
    {
        return $this->tourId;
    }

    /**
     * @param string|null $tourId
     *
     * @return self
     */
    public function setTourId(?string $tourId): self
    {
        $this->tourId = $tourId;
        return $this;
    }

    public function jsonSerialize()
    {
        return [
            'country'           => $this->country,
            'city'              => $this->city,
            'dateFrom'          => $this->dateFrom->format('Y-m-d'),
            'dateTill'          => $this->dateTill->format('Y-m-d'),
            'departureTicketId' => $this->departureTicketId,
            'returnTicketId'    => $this->returnTicketId,
            'hotelBookingId'    => $this->hotelBookingId,
            'visaId'            => $this->visaId,
            'tourId'            => $this->tourId,
        ];
    }
}
