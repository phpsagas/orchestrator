<?php

namespace PhpSagas\Orchestrator\Tests\_support\Implementation\TravelTourSaga\VisaCenterService;

use PhpSagas\Common\Message\CommandMessage;
use PhpSagas\Common\Message\ReplyMessageFactoryInterface;
use PhpSagas\Orchestrator\Tests\_support\Implementation\ReplyMessageProducer;

/**
 * @author Oleg Filatov <phpsagas@gmail.com>
 */
class VisaObtainingConsumer
{
    /** @var ReplyMessageFactoryInterface */
    private $replyMessageFactory;
    /** @var ReplyMessageProducer */
    private $replyMessageProducer;

    /** @var bool */
    private $isVisaObtainingHandlerBroken = false;

    public function __construct(
        ReplyMessageFactoryInterface $replyMessageFactory,
        ReplyMessageProducer $replyMessageProducer
    ) {
        $this->replyMessageFactory = $replyMessageFactory;
        $this->replyMessageProducer = $replyMessageProducer;
    }

    public function handleVisaObtained(CommandMessage $message)
    {
        if ($this->isVisaObtainingHandlerBroken) {
            $message = $this->replyMessageFactory->makeFailure($message->getSagaId(), $message->getId(), '{}');
        } else {
            $payload = json_encode(['visaId' => 4]);
            $message = $this->replyMessageFactory->makeSuccess($message->getSagaId(), $message->getId(), $payload);
        }

        $this->replyMessageProducer->send($message);
    }

    public function breakDownVisaObtaining(): void
    {
        $this->isVisaObtainingHandlerBroken = true;
    }

    public function reset(): void
    {
        $this->isVisaObtainingHandlerBroken = false;
    }
}
