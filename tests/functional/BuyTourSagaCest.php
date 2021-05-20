<?php

namespace PhpSagas\Orchestrator\Tests;

use Mockery\MockInterface;
use PhpSagas\Common\Message\DefaultCommandMessageFactory;
use PhpSagas\Common\Message\DefaultReplyMessageFactory;
use PhpSagas\Orchestrator\InstantiationEngine\DefaultSagaInstanceFactory;
use PhpSagas\Orchestrator\ExecutionEngine\NullSagaLocker;
use PhpSagas\Orchestrator\ExecutionEngine\SagaActionsProcessor;
use PhpSagas\Orchestrator\ExecutionEngine\SagaCommandProducer;
use PhpSagas\Orchestrator\ExecutionEngine\SagaCreator;
use PhpSagas\Orchestrator\ExecutionEngine\SagaExecutionStateSerializer;
use PhpSagas\Orchestrator\ExecutionEngine\SagaReplyHandler;
use PhpSagas\Orchestrator\ExecutionEngine\SagaStatusEnum;
use PhpSagas\Orchestrator\InstantiationEngine\SagaFactoryInterface;
use PhpSagas\Orchestrator\Tests\_support\Implementation\CommandExecutionDetectorInterface;
use PhpSagas\Orchestrator\Tests\_support\Implementation\InMemoryMessageIdGenerator;
use PhpSagas\Orchestrator\Tests\_support\Implementation\InMemoryMessageProducer;
use PhpSagas\Orchestrator\Tests\_support\Implementation\InMemorySagaInstanceRepository;
use PhpSagas\Orchestrator\Tests\_support\Implementation\JsonEncodeMessagePayloadSerializer;
use PhpSagas\Orchestrator\Tests\_support\Implementation\JsonEncodeSagaSerializer;
use PhpSagas\Orchestrator\Tests\_support\Implementation\ReplyMessageProducer;
use PhpSagas\Orchestrator\Tests\_support\Implementation\SagaExecutionDetectorInterface;
use PhpSagas\Orchestrator\Tests\_support\Implementation\SagaFactory;
use PhpSagas\Orchestrator\Tests\_support\Implementation\TravelTourSaga\Command\BookHotelCommand;
use PhpSagas\Orchestrator\Tests\_support\Implementation\TravelTourSaga\ReplyHandler\BookHotelReplyHandler;
use PhpSagas\Orchestrator\Tests\_support\Implementation\TravelTourSaga\Command\BookTicketsCommand;
use PhpSagas\Orchestrator\Tests\_support\Implementation\TravelTourSaga\ReplyHandler\BookTicketsReplyHandler;
use PhpSagas\Orchestrator\Tests\_support\Implementation\TravelTourSaga\Command\BuyTourCommand;
use PhpSagas\Orchestrator\Tests\_support\Implementation\TravelTourSaga\BuyTourSaga;
use PhpSagas\Orchestrator\Tests\_support\Implementation\TravelTourSaga\BuyTourSagaData;
use PhpSagas\Orchestrator\Tests\_support\Implementation\TravelTourSaga\Command\ConfirmHotelBookingCommand;
use PhpSagas\Orchestrator\Tests\_support\Implementation\TravelTourSaga\Command\ConfirmTicketsBookingCommand;
use PhpSagas\Orchestrator\Tests\_support\Implementation\TravelTourSaga\Command\ConfirmTourCommand;
use PhpSagas\Orchestrator\Tests\_support\Implementation\TravelTourSaga\FlightTicketService\TicketsBookingConsumer;
use PhpSagas\Orchestrator\Tests\_support\Implementation\TravelTourSaga\HotelService\HotelBookingConsumer;
use PhpSagas\Orchestrator\Tests\_support\Implementation\TravelTourSaga\Command\ObtainVisaCommand;
use PhpSagas\Orchestrator\Tests\_support\Implementation\TravelTourSaga\ReplyHandler\ObtainVisaReplyHandler;
use PhpSagas\Orchestrator\Tests\_support\Implementation\TravelTourSaga\Command\RejectHotelBookingCommand;
use PhpSagas\Orchestrator\Tests\_support\Implementation\TravelTourSaga\Command\RejectTicketsBookingCommand;
use PhpSagas\Orchestrator\Tests\_support\Implementation\TravelTourSaga\Command\RejectTourCommand;
use PhpSagas\Orchestrator\Tests\_support\Implementation\TravelTourSaga\TouristAgencyService\InMemoryTourRepository;
use PhpSagas\Orchestrator\Tests\_support\Implementation\TravelTourSaga\TouristAgencyService\Tour;
use PhpSagas\Orchestrator\Tests\_support\Implementation\TravelTourSaga\TouristAgencyService\TourService;
use PhpSagas\Orchestrator\Tests\_support\Implementation\TravelTourSaga\VisaCenterService\VisaObtainingConsumer;

/**
 * @author Oleg Filatov <phpsagas@gmail.com>
 */
class BuyTourSagaCest
{
    /** @var InMemoryTourRepository */
    private $tourRepo;
    /** @var TourService */
    private $tourService;
    /** @var SagaCreator */
    private $sagaCreator;
    /** @var InMemorySagaInstanceRepository */
    private $sagaInstanceRepo;
    /** @var SagaFactoryInterface */
    private $sagaFactory;
    /** @var JsonEncodeSagaSerializer */
    private $sagaSerializer;

    /** @var TicketsBookingConsumer */
    private $ticketsConsumer;
    /** @var HotelBookingConsumer */
    private $hotelConsumer;
    /** @var VisaObtainingConsumer */
    private $visaConsumer;

    /** @var SagaExecutionDetectorInterface|MockInterface */
    private $sagaExecutionDetector;
    /** @var CommandExecutionDetectorInterface|MockInterface */
    private $buyTourDetector;
    /** @var CommandExecutionDetectorInterface|MockInterface */
    private $confirmTourDetector;
    /** @var CommandExecutionDetectorInterface|MockInterface */
    private $rejectTourDetector;
    /** @var CommandExecutionDetectorInterface|MockInterface */
    private $bookTicketsDetector;
    /** @var CommandExecutionDetectorInterface|MockInterface */
    private $confirmTicketsDetector;
    /** @var CommandExecutionDetectorInterface|MockInterface */
    private $rejectTicketsDetector;
    /** @var CommandExecutionDetectorInterface|MockInterface */
    private $ticketsHandlerDetector;
    /** @var CommandExecutionDetectorInterface|MockInterface */
    private $bookHotelDetector;
    /** @var CommandExecutionDetectorInterface|MockInterface */
    private $rejectHotelDetector;
    /** @var CommandExecutionDetectorInterface|MockInterface */
    private $confirmHotelDetector;
    /** @var CommandExecutionDetectorInterface|MockInterface */
    private $hotelHandlerDetector;
    /** @var CommandExecutionDetectorInterface|MockInterface */
    private $obtainVisaDetector;
    /** @var CommandExecutionDetectorInterface|MockInterface */
    private $visaHandlerDetector;

    public function _before(FunctionalTester $I)
    {
        $this->tourRepo = new InMemoryTourRepository();
        $this->tourService = new TourService($this->tourRepo);

        $this->sagaExecutionDetector = \Mockery::mock(SagaExecutionDetectorInterface::class);
        $this->buyTourDetector = \Mockery::mock(CommandExecutionDetectorInterface::class);
        $this->confirmTourDetector = \Mockery::mock(CommandExecutionDetectorInterface::class);
        $this->rejectTourDetector = \Mockery::mock( CommandExecutionDetectorInterface::class);
        $this->bookTicketsDetector = \Mockery::mock(CommandExecutionDetectorInterface::class);
        $this->confirmTicketsDetector = \Mockery::mock(CommandExecutionDetectorInterface::class);
        $this->rejectTicketsDetector = \Mockery::mock(CommandExecutionDetectorInterface::class);
        $this->ticketsHandlerDetector = \Mockery::mock(CommandExecutionDetectorInterface::class);
        $this->bookHotelDetector = \Mockery::mock(CommandExecutionDetectorInterface::class);
        $this->confirmHotelDetector = \Mockery::mock(CommandExecutionDetectorInterface::class);
        $this->rejectHotelDetector = \Mockery::mock(CommandExecutionDetectorInterface::class);
        $this->hotelHandlerDetector = \Mockery::mock(CommandExecutionDetectorInterface::class);
        $this->obtainVisaDetector = \Mockery::mock(CommandExecutionDetectorInterface::class);
        $this->visaHandlerDetector = \Mockery::mock(CommandExecutionDetectorInterface::class);

        $buyTourCommand = new BuyTourCommand($this->tourService, $this->buyTourDetector);
        $rejectTourCommand = new RejectTourCommand($this->tourService, $this->rejectTourDetector);
        $confirmTourCommand = new ConfirmTourCommand($this->tourService, $this->confirmTourDetector);
        $bookTicketsCommand = new BookTicketsCommand($this->bookTicketsDetector);
        $confirmTicketsBookingCommand = new ConfirmTicketsBookingCommand($this->confirmTicketsDetector);
        $rejectTicketsBookingCommand = new RejectTicketsBookingCommand($this->rejectTicketsDetector);
        $bookTicketsReplyHandler = new BookTicketsReplyHandler($this->ticketsHandlerDetector);
        $bookHotelCommand = new BookHotelCommand($this->bookHotelDetector);
        $confirmHotelBookingCommand = new ConfirmHotelBookingCommand($this->confirmHotelDetector);
        $rejectHotelBookingCommand = new RejectHotelBookingCommand($this->rejectHotelDetector);
        $bookHotelReplyHandler = new BookHotelReplyHandler($this->hotelHandlerDetector);
        $obtainVisaCommand = new ObtainVisaCommand($this->obtainVisaDetector);
        $obtainVisaReplyHandler = new ObtainVisaReplyHandler($this->visaHandlerDetector);
        
        $this->sagaInstanceRepo = new InMemorySagaInstanceRepository();
        $this->sagaSerializer = new JsonEncodeSagaSerializer();
        $executionStateSerializer = new SagaExecutionStateSerializer();
        $messageIdGenerator = new InMemoryMessageIdGenerator();
        $replyMessageFactory = new DefaultReplyMessageFactory($messageIdGenerator);
        $this->sagaFactory = new SagaFactory(
            $this->sagaExecutionDetector,
            $buyTourCommand,
            $rejectTourCommand,
            $confirmTourCommand,
            $bookTicketsCommand,
            $confirmTicketsBookingCommand,
            $rejectTicketsBookingCommand,
            $bookTicketsReplyHandler,
            $bookHotelCommand,
            $confirmHotelBookingCommand,
            $rejectHotelBookingCommand,
            $bookHotelReplyHandler,
            $obtainVisaCommand,
            $obtainVisaReplyHandler
        );
        $replyMessageProducer = new ReplyMessageProducer();

        $this->hotelConsumer = new HotelBookingConsumer($replyMessageFactory, $replyMessageProducer);
        $this->ticketsConsumer = new TicketsBookingConsumer($replyMessageFactory, $replyMessageProducer);
        $this->visaConsumer = new VisaObtainingConsumer($replyMessageFactory, $replyMessageProducer);

        $messageProducer = new InMemoryMessageProducer($this->hotelConsumer, $this->ticketsConsumer, $this->visaConsumer);
        $messageFactory = new DefaultCommandMessageFactory();
        $payloadSerializer = new JsonEncodeMessagePayloadSerializer();
        $sagaCommandProducer = new SagaCommandProducer($messageProducer, $messageFactory, $payloadSerializer);
        $sagaLocker = new NullSagaLocker();

        $sagaActionsProcessor = new SagaActionsProcessor($this->sagaInstanceRepo, $this->sagaSerializer, $executionStateSerializer, $messageIdGenerator, $sagaLocker, $sagaCommandProducer);
        $sagaReplyHandler = new SagaReplyHandler($this->sagaInstanceRepo, $this->sagaSerializer, $executionStateSerializer, $this->sagaFactory, $sagaActionsProcessor);
        $replyMessageProducer->setSagaReplyHandler($sagaReplyHandler);
        $sagaInstanceFactory = new DefaultSagaInstanceFactory($this->sagaSerializer);

        $this->sagaCreator = new SagaCreator($this->sagaInstanceRepo, $sagaInstanceFactory, $sagaLocker, $sagaActionsProcessor);
    }

    /**
     * @param FunctionalTester $I
     *
     * @throws \Exception
     */
    public function testBuyTourSagaSuccessfulWorks(FunctionalTester $I): void
    {
        $sagaData = new BuyTourSagaData(
            'England',
            'London',
            new \DateTimeImmutable('2020-01-15'),
            new \DateTimeImmutable('2020-01-25')
        );

        $saga = $this->sagaFactory->create(BuyTourSaga::SAGA_TYPE);

        $this->sagaExecutionDetector->shouldReceive('starting')->once();
        $this->sagaExecutionDetector->shouldReceive('finished')->once();
        $this->sagaExecutionDetector->shouldReceive('failed')->never();

        $this->buyTourDetector->shouldReceive('execute')->once();
        $this->confirmTourDetector->shouldReceive('execute')->once();
        $this->rejectTourDetector->shouldReceive('execute')->never();

        $this->bookTicketsDetector->shouldReceive('execute')->once();
        $this->confirmTicketsDetector->shouldReceive('execute')->once();
        $this->rejectTicketsDetector->shouldReceive('execute')->never();
        $this->ticketsHandlerDetector->shouldReceive('execute')->once();

        $this->bookHotelDetector->shouldReceive('execute')->once();
        $this->confirmHotelDetector->shouldReceive('execute')->once();
        $this->rejectHotelDetector->shouldReceive('execute')->never();
        $this->hotelHandlerDetector->shouldReceive('execute')->once();

        $this->obtainVisaDetector->shouldReceive('execute')->once();
        $this->visaHandlerDetector->shouldReceive('execute')->once();

        $sagaInstance = $this->sagaCreator->create($saga, $sagaData);
        $sagaInstance = $this->sagaInstanceRepo->findSaga($sagaInstance->getSagaId());

        $I->assertEquals(SagaStatusEnum::FINISHED, $sagaInstance->getStatus());
        $I->assertEquals(BuyTourSaga::SAGA_TYPE, $sagaInstance->getSagaType());

        /** @var BuyTourSagaData $sagaData */
        $sagaData = $this->sagaSerializer->deserialize($sagaInstance->getSagaData(), $sagaInstance->getSagaDataType());

        $I->assertInstanceOf(BuyTourSagaData::class, $sagaData);
        $I->assertNotNull($sagaData->getTourId());
        $I->assertNotNull($sagaData->getHotelBookingId());
        $I->assertNotNull($sagaData->getVisaId());
        $I->assertNotNull($sagaData->getReturnTicketId());
        $I->assertNotNull($sagaData->getDepartureTicketId());
        $I->assertEquals('England', $sagaData->getCountry());
        $I->assertEquals('London', $sagaData->getCity());
        $I->assertEquals('2020-01-15', $sagaData->getDateFrom()->format('Y-m-d'));
        $I->assertEquals('2020-01-25', $sagaData->getDateTill()->format('Y-m-d'));

        $tour = $this->tourRepo->find($sagaData->getTourId());

        $I->assertEquals(Tour::STATUS_CONFIRMED, $tour->getStatus());
        $I->assertEquals($sagaData->getHotelBookingId(), $tour->getHotelBookingId());
        $I->assertEquals($sagaData->getVisaId(), $tour->getVisaId());
        $I->assertEquals($sagaData->getDepartureTicketId(), $tour->getDepartureTicketId());
        $I->assertEquals($sagaData->getReturnTicketId(), $tour->getReturnedTicketId());
        $I->assertEquals('England', $tour->getCountry());
        $I->assertEquals('London', $tour->getCity());
        $I->assertEquals('2020-01-15', $tour->getDateFrom()->format('Y-m-d'));
        $I->assertEquals('2020-01-25', $tour->getDateTill()->format('Y-m-d'));
    }

    /**
     * @param FunctionalTester $I
     *
     * @throws \Exception
     */
    public function testBuyTourSagaRolledBackBecauseTicketsBookingFailed(FunctionalTester $I): void
    {
        $sagaData = new BuyTourSagaData(
            'USA',
            'New York',
            new \DateTimeImmutable('2020-01-01'),
            new \DateTimeImmutable('2020-01-06')
        );

        $saga = $this->sagaFactory->create(BuyTourSaga::SAGA_TYPE);

        $this->sagaExecutionDetector->shouldReceive('starting')->once();
        $this->sagaExecutionDetector->shouldReceive('finished')->never();
        $this->sagaExecutionDetector->shouldReceive('failed')->once();

        $this->buyTourDetector->shouldReceive('execute')->once();
        $this->confirmTourDetector->shouldReceive('execute')->never();
        $this->rejectTourDetector->shouldReceive('execute')->once();

        $this->bookTicketsDetector->shouldReceive('execute')->once();
        $this->confirmTicketsDetector->shouldReceive('execute')->never();
        $this->rejectTicketsDetector->shouldReceive('execute')->never();
        $this->ticketsHandlerDetector->shouldReceive('execute')->once();

        $this->bookHotelDetector->shouldReceive('execute')->never();
        $this->confirmHotelDetector->shouldReceive('execute')->never();
        $this->rejectHotelDetector->shouldReceive('execute')->never();
        $this->hotelHandlerDetector->shouldReceive('execute')->never();

        $this->obtainVisaDetector->shouldReceive('execute')->never();
        $this->visaHandlerDetector->shouldReceive('execute')->never();

        $this->ticketsConsumer->breakDownTicketsBooking();

        $sagaInstance = $this->sagaCreator->create($saga, $sagaData);
        $sagaInstance = $this->sagaInstanceRepo->findSaga($sagaInstance->getSagaId());

        $I->assertEquals(SagaStatusEnum::FAILED, $sagaInstance->getStatus());
        $I->assertEquals(BuyTourSaga::SAGA_TYPE, $sagaInstance->getSagaType());

        /** @var BuyTourSagaData $sagaData */
        $sagaData = $this->sagaSerializer->deserialize($sagaInstance->getSagaData(), $sagaInstance->getSagaDataType());

        $I->assertInstanceOf(BuyTourSagaData::class, $sagaData);
        $I->assertNotNull($sagaData->getTourId());
        $I->assertNull($sagaData->getHotelBookingId());
        $I->assertNull($sagaData->getVisaId());
        $I->assertNull($sagaData->getReturnTicketId());
        $I->assertNull($sagaData->getDepartureTicketId());
        $I->assertEquals('USA', $sagaData->getCountry());
        $I->assertEquals('New York', $sagaData->getCity());
        $I->assertEquals('2020-01-01', $sagaData->getDateFrom()->format('Y-m-d'));
        $I->assertEquals('2020-01-06', $sagaData->getDateTill()->format('Y-m-d'));

        $tour = $this->tourRepo->find($sagaData->getTourId());

        $I->assertEquals(Tour::STATUS_REJECTED, $tour->getStatus());
        $I->assertNull($tour->getHotelBookingId());
        $I->assertNull($tour->getVisaId());
        $I->assertNull($tour->getDepartureTicketId());
        $I->assertNull($tour->getReturnedTicketId());
        $I->assertEquals('USA', $tour->getCountry());
        $I->assertEquals('New York', $tour->getCity());
        $I->assertEquals('2020-01-01', $tour->getDateFrom()->format('Y-m-d'));
        $I->assertEquals('2020-01-06', $tour->getDateTill()->format('Y-m-d'));
    }

    /**
     * @param FunctionalTester $I
     *
     * @throws \Exception
     */
    public function testBuyTourSagaRolledBackBecauseHotelBookingFailed(FunctionalTester $I): void
    {
        $sagaData = new BuyTourSagaData(
            'Italy',
            'Rome',
            new \DateTimeImmutable('2020-01-10'),
            new \DateTimeImmutable('2020-01-15')
        );

        $saga = $this->sagaFactory->create(BuyTourSaga::SAGA_TYPE);

        $this->sagaExecutionDetector->shouldReceive('starting')->once();
        $this->sagaExecutionDetector->shouldReceive('finished')->never();
        $this->sagaExecutionDetector->shouldReceive('failed')->once();

        $this->buyTourDetector->shouldReceive('execute')->once();
        $this->confirmTourDetector->shouldReceive('execute')->never();
        $this->rejectTourDetector->shouldReceive('execute')->once();

        $this->bookTicketsDetector->shouldReceive('execute')->once();
        $this->confirmTicketsDetector->shouldReceive('execute')->never();
        $this->rejectTicketsDetector->shouldReceive('execute')->once();
        $this->ticketsHandlerDetector->shouldReceive('execute')->once();

        $this->bookHotelDetector->shouldReceive('execute')->once();
        $this->confirmHotelDetector->shouldReceive('execute')->never();
        $this->rejectHotelDetector->shouldReceive('execute')->never();
        $this->hotelHandlerDetector->shouldReceive('execute')->once();

        $this->obtainVisaDetector->shouldReceive('execute')->never();
        $this->visaHandlerDetector->shouldReceive('execute')->never();

        $this->hotelConsumer->breakDownHotelBooking();

        $sagaInstance = $this->sagaCreator->create($saga, $sagaData);
        $sagaInstance = $this->sagaInstanceRepo->findSaga($sagaInstance->getSagaId());

        $I->assertEquals(SagaStatusEnum::FAILED, $sagaInstance->getStatus());
        $I->assertEquals(BuyTourSaga::SAGA_TYPE, $sagaInstance->getSagaType());

        /** @var BuyTourSagaData $sagaData */
        $sagaData = $this->sagaSerializer->deserialize($sagaInstance->getSagaData(), $sagaInstance->getSagaDataType());

        $I->assertInstanceOf(BuyTourSagaData::class, $sagaData);
        $I->assertNotNull($sagaData->getTourId());
        $I->assertNull($sagaData->getHotelBookingId());
        $I->assertNull($sagaData->getVisaId());
        $I->assertNotNull($sagaData->getReturnTicketId());
        $I->assertNotNull($sagaData->getDepartureTicketId());
        $I->assertEquals('Italy', $sagaData->getCountry());
        $I->assertEquals('Rome', $sagaData->getCity());
        $I->assertEquals('2020-01-10', $sagaData->getDateFrom()->format('Y-m-d'));
        $I->assertEquals('2020-01-15', $sagaData->getDateTill()->format('Y-m-d'));

        $tour = $this->tourRepo->find($sagaData->getTourId());

        $I->assertEquals(Tour::STATUS_REJECTED, $tour->getStatus());
        $I->assertNull($tour->getHotelBookingId());
        $I->assertNull($tour->getVisaId());
        $I->assertNull($tour->getDepartureTicketId());
        $I->assertNull($tour->getReturnedTicketId());
        $I->assertEquals('Italy', $tour->getCountry());
        $I->assertEquals('Rome', $tour->getCity());
        $I->assertEquals('2020-01-10', $tour->getDateFrom()->format('Y-m-d'));
        $I->assertEquals('2020-01-15', $tour->getDateTill()->format('Y-m-d'));
    }

    /**
     * @param FunctionalTester $I
     *
     * @throws \Exception
     */
    public function testBuyTourSagaRolledBackBecauseVisaObtainingFailed(FunctionalTester $I): void
    {
        $sagaData = new BuyTourSagaData(
            'Spain',
            'Madrid',
            new \DateTimeImmutable('2020-02-01'),
            new \DateTimeImmutable('2020-02-05')
        );

        $saga = $this->sagaFactory->create(BuyTourSaga::SAGA_TYPE);

        $this->sagaExecutionDetector->shouldReceive('starting')->once();
        $this->sagaExecutionDetector->shouldReceive('finished')->never();
        $this->sagaExecutionDetector->shouldReceive('failed')->once();

        $this->buyTourDetector->shouldReceive('execute')->once();
        $this->confirmTourDetector->shouldReceive('execute')->never();
        $this->rejectTourDetector->shouldReceive('execute')->once();

        $this->bookTicketsDetector->shouldReceive('execute')->once();
        $this->confirmTicketsDetector->shouldReceive('execute')->never();
        $this->rejectTicketsDetector->shouldReceive('execute')->once();
        $this->ticketsHandlerDetector->shouldReceive('execute')->once();

        $this->bookHotelDetector->shouldReceive('execute')->once();
        $this->confirmHotelDetector->shouldReceive('execute')->never();
        $this->rejectHotelDetector->shouldReceive('execute')->once();
        $this->hotelHandlerDetector->shouldReceive('execute')->once();

        $this->obtainVisaDetector->shouldReceive('execute')->once();
        $this->visaHandlerDetector->shouldReceive('execute')->once();

        $this->visaConsumer->breakDownVisaObtaining();

        $sagaInstance = $this->sagaCreator->create($saga, $sagaData);
        $sagaInstance = $this->sagaInstanceRepo->findSaga($sagaInstance->getSagaId());

        $I->assertEquals(SagaStatusEnum::FAILED, $sagaInstance->getStatus());
        $I->assertEquals(BuyTourSaga::SAGA_TYPE, $sagaInstance->getSagaType());

        /** @var BuyTourSagaData $sagaData */
        $sagaData = $this->sagaSerializer->deserialize($sagaInstance->getSagaData(), $sagaInstance->getSagaDataType());

        $I->assertInstanceOf(BuyTourSagaData::class, $sagaData);
        $I->assertNotNull($sagaData->getTourId());
        $I->assertNotNull($sagaData->getHotelBookingId());
        $I->assertNull($sagaData->getVisaId());
        $I->assertNotNull($sagaData->getReturnTicketId());
        $I->assertNotNull($sagaData->getDepartureTicketId());
        $I->assertEquals('Spain', $sagaData->getCountry());
        $I->assertEquals('Madrid', $sagaData->getCity());
        $I->assertEquals('2020-02-01', $sagaData->getDateFrom()->format('Y-m-d'));
        $I->assertEquals('2020-02-05', $sagaData->getDateTill()->format('Y-m-d'));

        $tour = $this->tourRepo->find($sagaData->getTourId());

        $I->assertEquals(Tour::STATUS_REJECTED, $tour->getStatus());
        $I->assertNull($tour->getHotelBookingId());
        $I->assertNull($tour->getVisaId());
        $I->assertNull($tour->getDepartureTicketId());
        $I->assertNull($tour->getReturnedTicketId());
        $I->assertEquals('Spain', $tour->getCountry());
        $I->assertEquals('Madrid', $tour->getCity());
        $I->assertEquals('2020-02-01', $tour->getDateFrom()->format('Y-m-d'));
        $I->assertEquals('2020-02-05', $tour->getDateTill()->format('Y-m-d'));
    }

    public function _after(FunctionalTester $I)
    {
        \Mockery::close();
        $this->sagaInstanceRepo->reset();
        $this->tourRepo->reset();
        $this->ticketsConsumer->reset();
        $this->hotelConsumer->reset();
        $this->visaConsumer->reset();
    }
}
