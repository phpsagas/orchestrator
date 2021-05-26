<?php

namespace PhpSagas\Orchestrator\Tests\_support\Implementation\TravelTourSaga\Command;

use PhpSagas\Contracts\SagaDataInterface;
use PhpSagas\Contracts\CommandDataInterface;
use PhpSagas\Orchestrator\Command\RemoteCommandInterface;
use PhpSagas\Orchestrator\Tests\_support\Implementation\CommandExecutionDetectorInterface;
use PhpSagas\Orchestrator\Tests\_support\Implementation\TravelTourSaga\CommandData\BookTicketsData;
use PhpSagas\Orchestrator\Tests\_support\Implementation\TravelTourSaga\BuyTourSagaData;

/**
 * @author Oleg Filatov <phpsagas@gmail.com>
 */
class BookTicketsCommand implements RemoteCommandInterface
{
    public const COMMAND_TYPE = 'book_tickets_command';

    /** @var CommandExecutionDetectorInterface */
    private $executionDetector;

    public function __construct(CommandExecutionDetectorInterface $executionDetector)
    {
        $this->executionDetector = $executionDetector;
    }

    public function getCommandType(): string
    {
        return self::COMMAND_TYPE;
    }

    public function getSagaDataClassName(): string
    {
        return BuyTourSagaData::class;
    }

    /**
     * @param SagaDataInterface|BuyTourSagaData $sagaData
     *
     * @return CommandDataInterface
     */
    public function getCommandData(SagaDataInterface $sagaData): CommandDataInterface
    {
        $this->executionDetector->execute();
        return new BookTicketsData(
            $sagaData->getCountry(),
            $sagaData->getCountry(),
            $sagaData->getDateFrom(),
            $sagaData->getDateTill()
        );
    }
}
