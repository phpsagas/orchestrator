<?php

namespace PhpSagas\Orchestrator\Tests\_support\Implementation\TravelTourSaga\CommandData;

use PhpSagas\Orchestrator\Command\CommandDataInterface;

/**
 * @author Oleg Filatov <phpsagas@gmail.com>
 */
class BookHotelData implements CommandDataInterface, \JsonSerializable
{
    /** @var string */
    private $country;
    /** @var string */
    private $city;
    /** @var \DateTimeImmutable */
    private $dateFrom;
    /** @var \DateTimeImmutable */
    private $dateTill;

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

    public function jsonSerialize()
    {
        return [
            'country'  => $this->country,
            'city'     => $this->city,
            'dateFrom' => $this->dateFrom,
            'dateTill' => $this->dateTill,
        ];
    }
}
