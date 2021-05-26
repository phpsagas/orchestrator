<?php

namespace PhpSagas\Orchestrator\Tests\_support\Implementation\TravelTourSaga\HotelService;

use PhpSagas\Contracts\CommandMessageInterface;
use PhpSagas\Contracts\ReplyMessageFactoryInterface;
use PhpSagas\Orchestrator\Tests\_support\Implementation\ReplyMessageProducer;

/**
 * @author Oleg Filatov <phpsagas@gmail.com>
 */
class HotelBookingConsumer
{
    /** @var ReplyMessageFactoryInterface */
    private $replyMessageFactory;
    /** @var ReplyMessageProducer */
    private $replyMessageProducer;

    /** @var bool */
    private $isHotelBookingHandlerBroken = false;

    public function __construct(
        ReplyMessageFactoryInterface $replyMessageFactory,
        ReplyMessageProducer $replyMessageProducer
    ) {
        $this->replyMessageFactory = $replyMessageFactory;
        $this->replyMessageProducer = $replyMessageProducer;
    }

    public function handleHotelBooked(CommandMessageInterface $message): void
    {
        if ($this->isHotelBookingHandlerBroken) {
            $message = $this->replyMessageFactory->makeFailure($message->getSagaId(), $message->getId(), '{}');
        } else {
            $payload = json_encode(['hotelBookingId' => 1]);
            $message = $this->replyMessageFactory->makeSuccess($message->getSagaId(), $message->getId(), $payload);
        }

        $this->replyMessageProducer->send($message);
    }

    public function handleBookingRejected(CommandMessageInterface $message): void
    {
        $this->replyMessageProducer->send(
            $this->replyMessageFactory->makeSuccess($message->getSagaId(), $message->getId(), '{}')
        );
    }

    public function handleBookingConfirmed(CommandMessageInterface $message): void
    {
        $this->replyMessageProducer->send(
            $this->replyMessageFactory->makeSuccess($message->getSagaId(), $message->getId(), '{}')
        );
    }

    public function breakDownHotelBooking(): void
    {
        $this->isHotelBookingHandlerBroken = true;
    }

    public function reset(): void
    {
        $this->isHotelBookingHandlerBroken = false;
    }
}
