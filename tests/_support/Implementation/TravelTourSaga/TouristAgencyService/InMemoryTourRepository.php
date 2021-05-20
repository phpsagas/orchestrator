<?php

namespace PhpSagas\Orchestrator\Tests\_support\Implementation\TravelTourSaga\TouristAgencyService;

/**
 * @author Oleg Filatov <phpsagas@gmail.com>
 */
class InMemoryTourRepository
{
    /** @var Tour[] */
    private $tours;
    /** @var int int */
    private $nextId = 1;

    public function save(Tour $tour): string
    {
        if (is_null($tour->getId())) {
            $tour->setId((string)$this->nextId++);
            $this->tours[$tour->getId()] = $tour;
        } else {
            $this->tours[$tour->getId()] = $tour;
        }

        return $tour->getId();
    }

    public function find(string $tourId): Tour
    {
        if (isset($this->tours[$tourId])) {
            return $this->tours[$tourId];
        }

        throw new \InvalidArgumentException('Tour not found: ' . $tourId);
    }

    public function reset(): void
    {
        $this->tours = [];
        $this->nextId = 1;
    }
}
