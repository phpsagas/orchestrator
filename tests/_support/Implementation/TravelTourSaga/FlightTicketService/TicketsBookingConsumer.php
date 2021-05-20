<?php

namespace PhpSagas\Orchestrator\Tests\_support\Implementation\TravelTourSaga\FlightTicketService;

use PhpSagas\Common\Message\CommandMessage;
use PhpSagas\Common\Message\ReplyMessageFactoryInterface;
use PhpSagas\Orchestrator\Tests\_support\Implementation\ReplyMessageProducer;

/**
 * @author Oleg Filatov <phpsagas@gmail.com>
 */
class TicketsBookingConsumer
{
    /** @var ReplyMessageFactoryInterface */
    private $replyMessageFactory;
    /** @var ReplyMessageProducer */
    private $replyMessageProducer;

    /** @var bool */
    private $isTicketsBookingHandlerBroken = false;

    public function __construct(
        ReplyMessageFactoryInterface $replyMessageFactory,
        ReplyMessageProducer $replyMessageProducer
    ) {
        $this->replyMessageFactory = $replyMessageFactory;
        $this->replyMessageProducer = $replyMessageProducer;
    }

    public function handleTicketsBooked(CommandMessage $message)
    {
        if ($this->isTicketsBookingHandlerBroken) {
            $message = $this->replyMessageFactory->makeFailure($message->getSagaId(), $message->getId(), '{}');
        } else {
            $payload = json_encode(['departureTicketId' => 2, 'returnTicketId' => 3]);
            $message = $this->replyMessageFactory->makeSuccess($message->getSagaId(), $message->getId(), $payload);
        }

        $this->replyMessageProducer->send($message);
    }

    public function handleBookingRejected(CommandMessage $message)
    {
        $this->replyMessageProducer->send(
            $this->replyMessageFactory->makeSuccess($message->getSagaId(), $message->getId(), '{}')
        );
    }

    public function handleBookingConfirmed(CommandMessage $message)
    {
        $this->replyMessageProducer->send(
            $this->replyMessageFactory->makeSuccess($message->getSagaId(), $message->getId(), '{}')
        );
    }

    public function breakDownTicketsBooking(): void
    {
        $this->isTicketsBookingHandlerBroken = true;
    }

    public function reset(): void
    {
        $this->isTicketsBookingHandlerBroken = false;
    }
}
