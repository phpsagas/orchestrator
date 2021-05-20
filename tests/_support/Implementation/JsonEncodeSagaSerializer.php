<?php

namespace PhpSagas\Orchestrator\Tests\_support\Implementation;

use PhpSagas\Orchestrator\BuildEngine\EmptySagaData;
use PhpSagas\Orchestrator\BuildEngine\SagaDataInterface;
use PhpSagas\Orchestrator\ExecutionEngine\SagaSerializerInterface;
use PhpSagas\Orchestrator\Tests\_support\Implementation\PublishArticleSaga\PublishArticleSagaData;
use PhpSagas\Orchestrator\Tests\_support\Implementation\TravelTourSaga\BuyTourSagaData;

/**
 * @author Oleg Filatov <phpsagas@gmail.com>
 */
class JsonEncodeSagaSerializer implements SagaSerializerInterface
{
    public function serialize(SagaDataInterface $sagaData): string
    {
        return json_encode($sagaData);
    }

    /**
     * @param string $sagaData
     * @param string $type
     *
     * @return SagaDataInterface
     * @throws \Exception
     */
    public function deserialize(string $sagaData, string $type): SagaDataInterface
    {
        $decodedData = json_decode($sagaData, true);

        switch ($type) {
            case PublishArticleSagaData::class:
                return new PublishArticleSagaData($decodedData['articleTitle'], $decodedData['articleBody']);
            case BuyTourSagaData::class:
                $data = new BuyTourSagaData(
                    $decodedData['country'],
                    $decodedData['city'],
                    new \DateTimeImmutable($decodedData['dateFrom']),
                    new \DateTimeImmutable($decodedData['dateTill'])
                );

                return $data
                    ->setVisaId($decodedData['visaId'])
                    ->setTourId($decodedData['tourId'])
                    ->setHotelBookingId($decodedData['hotelBookingId'])
                    ->setReturnTicketId($decodedData['returnTicketId'])
                    ->setDepartureTicketId($decodedData['departureTicketId']);
            default:
                return new EmptySagaData();
        }
    }
}
