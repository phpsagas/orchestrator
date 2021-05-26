<?php

namespace PhpSagas\Orchestrator\Tests\_support\Implementation\TravelTourSaga;

use PhpSagas\Contracts\SagaDataInterface;
use PhpSagas\Orchestrator\BuildEngine\SagaDefinition;
use PhpSagas\Orchestrator\BuildEngine\SagaDefinitionBuilder;
use PhpSagas\Orchestrator\BuildEngine\SagaInterface;
use PhpSagas\Orchestrator\BuildEngine\StepBuilder;
use PhpSagas\Orchestrator\Tests\_support\Implementation\SagaExecutionDetectorInterface;
use PhpSagas\Orchestrator\Tests\_support\Implementation\TravelTourSaga\Command\BookHotelCommand;
use PhpSagas\Orchestrator\Tests\_support\Implementation\TravelTourSaga\Command\BookTicketsCommand;
use PhpSagas\Orchestrator\Tests\_support\Implementation\TravelTourSaga\Command\BuyTourCommand;
use PhpSagas\Orchestrator\Tests\_support\Implementation\TravelTourSaga\Command\ConfirmHotelBookingCommand;
use PhpSagas\Orchestrator\Tests\_support\Implementation\TravelTourSaga\Command\ConfirmTicketsBookingCommand;
use PhpSagas\Orchestrator\Tests\_support\Implementation\TravelTourSaga\Command\ConfirmTourCommand;
use PhpSagas\Orchestrator\Tests\_support\Implementation\TravelTourSaga\Command\ObtainVisaCommand;
use PhpSagas\Orchestrator\Tests\_support\Implementation\TravelTourSaga\Command\RejectHotelBookingCommand;
use PhpSagas\Orchestrator\Tests\_support\Implementation\TravelTourSaga\Command\RejectTicketsBookingCommand;
use PhpSagas\Orchestrator\Tests\_support\Implementation\TravelTourSaga\Command\RejectTourCommand;
use PhpSagas\Orchestrator\Tests\_support\Implementation\TravelTourSaga\ReplyHandler\BookHotelReplyHandler;
use PhpSagas\Orchestrator\Tests\_support\Implementation\TravelTourSaga\ReplyHandler\BookTicketsReplyHandler;
use PhpSagas\Orchestrator\Tests\_support\Implementation\TravelTourSaga\ReplyHandler\ObtainVisaReplyHandler;

/**
 * @author Oleg Filatov <phpsagas@gmail.com>
 */
class BuyTourSaga implements SagaInterface
{
    public const SAGA_TYPE = 'buy_tour_saga';

    /** @var SagaDataInterface|null */
    private $initialData;
    /** @var SagaDefinition */
    private $definition;
    /** @var SagaExecutionDetectorInterface */
    private $executionDetector;

    public function __construct(
        SagaExecutionDetectorInterface $executionDetector,
        BuyTourCommand $buyTourCommand,
        RejectTourCommand $rejectTourCommand,
        ConfirmTourCommand $confirmTourCommand,
        BookTicketsCommand $bookTicketsCommand,
        ConfirmTicketsBookingCommand $confirmTicketsBookingCommand,
        RejectTicketsBookingCommand $rejectTicketsBookingCommand,
        BookTicketsReplyHandler $bookTicketsReplyHandler,
        BookHotelCommand $bookHotelCommand,
        ConfirmHotelBookingCommand $confirmHotelBookingCommand,
        RejectHotelBookingCommand $rejectHotelBookingCommand,
        BookHotelReplyHandler $bookHotelReplyHandler,
        ObtainVisaCommand $obtainVisaCommand,
        ObtainVisaReplyHandler $obtainVisaReplyHandler
    ) {
        $this->executionDetector = $executionDetector;

        $steps = $this
            ->step()
            ->localCommand($buyTourCommand) // <-- compensatable transaction
            ->withCompensation($rejectTourCommand) // <-- compensating transaction
            ->step()
            ->remoteCommand($bookTicketsCommand)
            ->onReply($bookTicketsReplyHandler)
            ->withCompensation($rejectTicketsBookingCommand)
            ->step()
            ->remoteCommand($bookHotelCommand)
            ->onReply($bookHotelReplyHandler)
            ->withCompensation($rejectHotelBookingCommand)
            ->step()
            ->remoteCommand($obtainVisaCommand) // <-- pivot transaction
            ->onReply($obtainVisaReplyHandler)
            ->step()
            ->remoteCommand($confirmHotelBookingCommand) // <-- retryable transaction
            ->step()
            ->remoteCommand($confirmTicketsBookingCommand)
            ->step()
            ->localCommand($confirmTourCommand);
        ;

        $this->definition = $steps->build();
    }

    public function getSagaDefinition(): SagaDefinition
    {
        return $this->definition;
    }

    public function getSagaType(): string
    {
        return self::SAGA_TYPE;
    }

    public function getSagaInitialData(): ?SagaDataInterface
    {
        return $this->initialData;
    }

    public function onStarted(string $sagaId, SagaDataInterface $data): void
    {
        $this->executionDetector->starting();
    }

    public function onFinished(string $sagaId, SagaDataInterface $data): void
    {
        $this->executionDetector->finished();
    }

    public function onFailed(string $sagaId, SagaDataInterface $data): void
    {
        $this->executionDetector->failed();
    }

    private function step(): StepBuilder
    {
        return new StepBuilder(new SagaDefinitionBuilder());
    }
}
