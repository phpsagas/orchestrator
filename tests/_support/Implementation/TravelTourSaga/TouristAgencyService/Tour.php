<?php

namespace PhpSagas\Orchestrator\Tests\_support\Implementation\TravelTourSaga\TouristAgencyService;

/**
 * @author Oleg Filatov <phpsagas@gmail.com>
 */
class Tour
{
    /** @var string */
    public const STATUS_PENDING = 'pending';
    /** @var string */
    public const STATUS_CONFIRMED = 'confirmed';
    /** @var string */
    public const STATUS_REJECTED = 'rejected';

    /** @var string|null */
    private $id;
    /** @var string */
    private $country;
    /** @var string */
    private $city;
    /** @var \DateTimeImmutable */
    private $dateFrom;
    /** @var \DateTimeImmutable */
    private $dateTill;
    /** @var string */
    private $status;
    /** @var string|null */
    private $departureTicketId;
    /** @var string|null */
    private $returnedTicketId;
    /** @var string|null */
    private $hotelBookingId;
    /** @var string|null */
    private $medicalInsurancePolicyId;
    /** @var string|null */
    private $visaId;

    private function __construct(
        string $country,
        string $city,
        \DateTimeImmutable $dateFrom,
        \DateTimeImmutable $dateTill
    ) {
        $this->country = $country;
        $this->city = $city;
        $this->dateFrom = $dateFrom;
        $this->dateTill = $dateTill;
        $this->status = self::STATUS_PENDING;
    }

    public static function createPending(
        string $country,
        string $city,
        \DateTimeImmutable $dateFrom,
        \DateTimeImmutable $dateTill
    ): self {
        return new self($country, $city, $dateFrom, $dateTill);
    }

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @param string|null $id
     *
     * @return Tour
     */
    public function setId(?string $id): Tour
    {
        $this->id = $id;
        return $this;
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
     * @return Tour
     */
    public function setDepartureTicketId(?string $departureTicketId): Tour
    {
        $this->departureTicketId = $departureTicketId;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getReturnedTicketId(): ?string
    {
        return $this->returnedTicketId;
    }

    /**
     * @param string|null $returnedTicketId
     *
     * @return Tour
     */
    public function setReturnedTicketId(?string $returnedTicketId): Tour
    {
        $this->returnedTicketId = $returnedTicketId;
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
     * @return Tour
     */
    public function setHotelBookingId(?string $hotelBookingId): Tour
    {
        $this->hotelBookingId = $hotelBookingId;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getMedicalInsurancePolicyId(): ?string
    {
        return $this->medicalInsurancePolicyId;
    }

    /**
     * @param string|null $medicalInsurancePolicyId
     *
     * @return Tour
     */
    public function setMedicalInsurancePolicyId(?string $medicalInsurancePolicyId): Tour
    {
        $this->medicalInsurancePolicyId = $medicalInsurancePolicyId;
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
     * @return Tour
     */
    public function setVisaId(?string $visaId): Tour
    {
        $this->visaId = $visaId;
        return $this;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    public function confirm(): self
    {
        $this->status = self::STATUS_CONFIRMED;
        return $this;
    }

    public function reject(): self
    {
        $this->status = self::STATUS_REJECTED;
        return $this;
    }
}
