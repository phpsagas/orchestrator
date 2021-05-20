<?php

namespace PhpSagas\Orchestrator\Tests;

use PhpSagas\Common\Message\CommandMessage;
use PhpSagas\Common\Message\DefaultCommandMessageFactory;
use PhpSagas\Common\Message\DefaultReplyMessageFactory;
use PhpSagas\Common\Message\ReplyMessage;
use PhpSagas\Common\Message\ReplyMessageFactoryInterface;
use PhpSagas\Orchestrator\InstantiationEngine\DefaultSagaInstanceFactory;
use PhpSagas\Orchestrator\BuildEngine\ReplyHandlerInterface;
use PhpSagas\Orchestrator\BuildEngine\SagaDataInterface;
use PhpSagas\Orchestrator\BuildEngine\SagaDefinitionBuilder;
use PhpSagas\Orchestrator\BuildEngine\SagaInterface;
use PhpSagas\Orchestrator\BuildEngine\StepBuilder;
use PhpSagas\Orchestrator\Command\CommandDataInterface;
use PhpSagas\Orchestrator\Command\LocalCommandException;
use PhpSagas\Orchestrator\Command\LocalCommandInterface;
use PhpSagas\Orchestrator\BuildEngine\SagaDefinition;
use PhpSagas\Orchestrator\Command\RemoteCommandInterface;
use PhpSagas\Orchestrator\ExecutionEngine\MessageProducerInterface;
use PhpSagas\Orchestrator\ExecutionEngine\NullSagaLocker;
use PhpSagas\Orchestrator\ExecutionEngine\SagaActionsProcessor;
use PhpSagas\Orchestrator\ExecutionEngine\SagaCommandProducer;
use PhpSagas\Orchestrator\ExecutionEngine\SagaCreator;
use PhpSagas\Orchestrator\ExecutionEngine\SagaExecutionStateSerializer;
use PhpSagas\Orchestrator\ExecutionEngine\SagaReplyHandler;
use PhpSagas\Orchestrator\InstantiationEngine\SagaFactoryInterface;
use PhpSagas\Orchestrator\Tests\_support\Implementation\CommandExecutionDetectorInterface;
use PhpSagas\Orchestrator\BuildEngine\EmptySagaData;
use PhpSagas\Orchestrator\Tests\_support\Implementation\InMemoryMessageIdGenerator;
use PhpSagas\Orchestrator\Tests\_support\Implementation\InMemorySagaInstanceRepository;
use PhpSagas\Orchestrator\Tests\_support\Implementation\JsonEncodeMessagePayloadSerializer;
use PhpSagas\Orchestrator\Tests\_support\Implementation\JsonEncodeSagaSerializer;
use PhpSagas\Orchestrator\Tests\_support\Implementation\ReplyMessageProducer;

/**
 * @author Oleg Filatov <phpsagas@gmail.com>
 */
class SyntheticSagaCest
{
    /** @var InMemorySagaInstanceRepository */
    private $instanceRepo;
    private $sagaSerializer;
    private $instanceFactory;
    private $stateSerializer;
    private $messageIdGenerator;
    private $replyMessageFactory;
    /** @var ReplyMessageProducer */
    private $replyMessageProducer;
    private $messageFactory;
    private $payloadSerializer;
    private $sagaLocker;

    public function _before()
    {
        $this->instanceRepo = new InMemorySagaInstanceRepository();
        $this->sagaSerializer = new JsonEncodeSagaSerializer();
        $this->instanceFactory = new DefaultSagaInstanceFactory($this->sagaSerializer);
        $this->stateSerializer = new SagaExecutionStateSerializer();
        $this->messageIdGenerator = new InMemoryMessageIdGenerator();
        $this->replyMessageFactory = new DefaultReplyMessageFactory($this->messageIdGenerator);
        $this->replyMessageProducer = new ReplyMessageProducer();
        $this->messageFactory = new DefaultCommandMessageFactory();
        $this->payloadSerializer = new JsonEncodeMessagePayloadSerializer();
        $this->sagaLocker = new NullSagaLocker();
    }

    /**
     * @throws \PhpSagas\Orchestrator\Command\LocalCommandException
     */
    public function testSkipStepsWithoutCompensationOnSagaRollback()
    {
        $sagaData = new EmptySagaData();
        $executionDetector = \Mockery::mock(CommandExecutionDetectorInterface::class);
        $executionDetector->shouldReceive('execute')->once();

        $localCommand = new class($sagaData) implements LocalCommandInterface
        {
            private $sagaData;

            public function __construct($sagaData) { $this->sagaData = $sagaData; }

            public function getSagaDataType(): string { return get_class($this->sagaData); }
            public function execute(SagaDataInterface $sagaData): void {}
        };

        $localCompensation = new class($executionDetector) implements LocalCommandInterface
        {
            /** @var CommandExecutionDetectorInterface */
            private $detector;

            public function __construct($detector) { $this->detector = $detector; }

            public function getSagaDataType(): string { return EmptySagaData::class; }
            public function execute(SagaDataInterface $sagaData): void { $this->detector->execute(); }
        };

        $remoteCommand = new class($sagaData) implements RemoteCommandInterface
        {
            private $sagaData;

            public function __construct($sagaData) { $this->sagaData= $sagaData; }
            public function getCommandType(): string { return 'dummy'; }
            public function getSagaDataClassName(): string { return get_class($this->sagaData); }
            public function getCommandData(SagaDataInterface $sagaData): CommandDataInterface { return new class implements CommandDataInterface{}; }
        };

        $saga = new class($localCommand, $localCompensation, $remoteCommand) implements SagaInterface
        {
            private $localCommand;
            private $localCompensation;
            private $remoteCommand;

            public function __construct($localCommand, $localCompensation, $remoteCommand)
            {
                $this->localCommand = $localCommand;
                $this->localCompensation = $localCompensation;
                $this->remoteCommand = $remoteCommand;
            }

            public function getSagaDefinition(): SagaDefinition
            {
                $steps = $this
                    ->step()
                    ->localCommand($this->localCommand)
                    ->withCompensation($this->localCompensation)
                    ->step()
                    ->localCommand($this->localCommand)
                    ->step()
                    ->localCommand($this->localCommand)
                    ->step()
                    ->remoteCommand($this->remoteCommand);

                return $steps->build();
            }

            public function getSagaType(): string
            {
                return 'dummy';
            }

            public function getSagaInitialData(): ?SagaDataInterface
            {
                return new class implements SagaDataInterface {};
            }

            public function onStarted(string $sagaId, SagaDataInterface $data): void {}

            public function onFinished(string $sagaId, SagaDataInterface $data): void {}

            public function onFailed(string $sagaId, SagaDataInterface $data): void {}

            private function step(): StepBuilder { return new StepBuilder(new SagaDefinitionBuilder()); }
        };

        $sagaFactory = new class($saga) implements SagaFactoryInterface
        {
            private $saga;

            public function __construct($saga) { $this->saga = $saga; }
            public function create(string $sagaType, SagaDataInterface $sagaInitialData = null): SagaInterface { return $this->saga; }
        };

        $messageProducer = new class($this->replyMessageProducer, $this->replyMessageFactory) implements MessageProducerInterface
        {
            /** @var ReplyMessageProducer */
            private $replyMessageProducer;
            /** @var ReplyMessageFactoryInterface */
            private $replyMessageFactory;

            public function __construct($replyMessageProducer, $replyMessageFactory) {
                $this->replyMessageProducer = $replyMessageProducer;
                $this->replyMessageFactory = $replyMessageFactory;
            }

            public function send(CommandMessage $message): void
            {
                // FAILURE for compensation starting
                $this->replyMessageProducer->send($this->replyMessageFactory->makeFailure($message->getSagaId(), $message->getId(), '{}'));
            }
        };

        $sagaCommandProducer = new SagaCommandProducer($messageProducer, $this->messageFactory, $this->payloadSerializer);
        $sagaActionsProcessor = new SagaActionsProcessor($this->instanceRepo, $this->sagaSerializer, $this->stateSerializer, $this->messageIdGenerator, $this->sagaLocker, $sagaCommandProducer);
        $sagaReplyHandler = new SagaReplyHandler($this->instanceRepo, $this->sagaSerializer, $this->stateSerializer, $sagaFactory, $sagaActionsProcessor);
        $this->replyMessageProducer->setSagaReplyHandler($sagaReplyHandler);

        $sagaCreator = new SagaCreator($this->instanceRepo, $this->instanceFactory, $this->sagaLocker, $sagaActionsProcessor);
        $sagaCreator->create($saga, $sagaData);
    }

    public function testLogicExceptionThrownWhenTryCompensateCompensation(FunctionalTester $I)
    {
        $sagaData = new EmptySagaData();

        $localCommand = new class($sagaData) implements LocalCommandInterface
        {
            private $sagaData;

            public function __construct($sagaData) { $this->sagaData = $sagaData; }

            public function getSagaDataType(): string { return get_class($this->sagaData); }
            public function execute(SagaDataInterface $sagaData): void {}
        };

        $localCompensation = new class() implements LocalCommandInterface
        {
            public function __construct() { }

            public function getSagaDataType(): string { return EmptySagaData::class; }
            public function execute(SagaDataInterface $sagaData): void {
                throw new LocalCommandException('compensation exception');
            }
        };

        $remoteCommand = new class($sagaData) implements RemoteCommandInterface
        {
            private $sagaData;

            public function __construct($sagaData) { $this->sagaData= $sagaData; }
            public function getCommandType(): string { return 'dummy'; }
            public function getSagaDataClassName(): string { return get_class($this->sagaData); }
            public function getCommandData(SagaDataInterface $sagaData): CommandDataInterface { return new class implements CommandDataInterface{}; }
        };

        $saga = new class($localCommand, $localCompensation, $remoteCommand) implements SagaInterface
        {
            private $localCommand;
            private $localCompensation;
            private $remoteCommand;

            public function __construct($localCommand, $localCompensation, $remoteCommand)
            {
                $this->localCommand = $localCommand;
                $this->localCompensation = $localCompensation;
                $this->remoteCommand = $remoteCommand;
            }

            public function getSagaDefinition(): SagaDefinition
            {
                $steps = $this
                    ->step()
                    ->localCommand($this->localCommand)
                    ->withCompensation($this->localCompensation)
                    ->step()
                    ->remoteCommand($this->remoteCommand);

                return $steps->build();
            }

            public function getSagaType(): string
            {
                return 'dummy';
            }

            public function getSagaInitialData(): ?SagaDataInterface
            {
                return new class implements SagaDataInterface {};
            }

            public function onStarted(string $sagaId, SagaDataInterface $data): void {}

            public function onFinished(string $sagaId, SagaDataInterface $data): void {}

            public function onFailed(string $sagaId, SagaDataInterface $data): void {}

            private function step(): StepBuilder { return new StepBuilder(new SagaDefinitionBuilder()); }
        };

        $sagaFactory = new class($saga) implements SagaFactoryInterface
        {
            private $saga;

            public function __construct($saga) { $this->saga = $saga; }
            public function create(string $sagaType, SagaDataInterface $sagaInitialData = null): SagaInterface { return $this->saga; }
        };

        $messageProducer = new class($this->replyMessageProducer, $this->replyMessageFactory) implements MessageProducerInterface
        {
            /** @var ReplyMessageProducer */
            private $replyMessageProducer;
            /** @var ReplyMessageFactoryInterface */
            private $replyMessageFactory;

            public function __construct($replyMessageProducer, $replyMessageFactory) {
                $this->replyMessageProducer = $replyMessageProducer;
                $this->replyMessageFactory = $replyMessageFactory;
            }

            public function send(CommandMessage $message): void
            {
                // FAILURE for compensation starting
                $this->replyMessageProducer->send($this->replyMessageFactory->makeFailure($message->getSagaId(), $message->getId(), '{}'));
            }
        };

        $sagaCommandProducer = new SagaCommandProducer($messageProducer, $this->messageFactory, $this->payloadSerializer);
        $sagaActionsProcessor = new SagaActionsProcessor($this->instanceRepo, $this->sagaSerializer, $this->stateSerializer, $this->messageIdGenerator, $this->sagaLocker, $sagaCommandProducer);
        $sagaReplyHandler = new SagaReplyHandler($this->instanceRepo, $this->sagaSerializer, $this->stateSerializer, $sagaFactory, $sagaActionsProcessor);
        $this->replyMessageProducer->setSagaReplyHandler($sagaReplyHandler);

        $sagaCreator = new SagaCreator($this->instanceRepo, $this->instanceFactory, $this->sagaLocker, $sagaActionsProcessor);

        $I->expectThrowable(\LogicException::class, function () use ($sagaCreator, $saga, $sagaData) {
            $sagaCreator->create($saga, $sagaData);
        });
    }

    /**
     * @throws LocalCommandException
     */
    public function testCompensationReplyHandlerWorks()
    {
        $sagaData = new EmptySagaData();
        $detector = \Mockery::mock(CommandExecutionDetectorInterface::class);
        $detector->shouldReceive('execute')->once();

        $remoteCommandSuccess = new class($sagaData) implements RemoteCommandInterface
        {
            private $sagaData;

            public function __construct($sagaData) { $this->sagaData= $sagaData; }
            public function getCommandType(): string { return 'success'; }
            public function getSagaDataClassName(): string { return get_class($this->sagaData); }
            public function getCommandData(SagaDataInterface $sagaData): CommandDataInterface { return new class implements CommandDataInterface{}; }
        };

        $remoteCommandFailure = new class($sagaData) implements RemoteCommandInterface
        {
            private $sagaData;

            public function __construct($sagaData) { $this->sagaData= $sagaData; }
            public function getCommandType(): string { return 'failure'; }
            public function getSagaDataClassName(): string { return get_class($this->sagaData); }
            public function getCommandData(SagaDataInterface $sagaData): CommandDataInterface { return new class implements CommandDataInterface{}; }
        };

        $replyHandler = new class($detector) implements ReplyHandlerInterface
        {
            /** @var CommandExecutionDetectorInterface */
            private $detector;

            public function __construct($detector) { $this->detector = $detector; }

            public function handle(ReplyMessage $message, SagaDataInterface $sagaData): void
            {
                $this->detector->execute();
            }
        };

        $saga = new class($remoteCommandSuccess, $remoteCommandFailure, $replyHandler) implements SagaInterface
        {
            private $remoteCommandSuccess;
            private $remoteCommandFailure;
            private $replyHandler;

            public function __construct($commandSuccess, $commandFailure, $replyHandler)
            {
                $this->remoteCommandSuccess = $commandSuccess;
                $this->remoteCommandFailure = $commandFailure;
                $this->replyHandler = $replyHandler;
            }

            public function getSagaDefinition(): SagaDefinition
            {
                $steps = $this
                    ->step()
                    ->remoteCommand($this->remoteCommandSuccess)
                    ->withCompensation($this->remoteCommandSuccess)
                    ->onReply($this->replyHandler)
                    ->step()
                    ->remoteCommand($this->remoteCommandFailure);

                return $steps->build();
            }

            public function getSagaType(): string
            {
                return 'dummy';
            }

            public function getSagaInitialData(): ?SagaDataInterface
            {
                return new class implements SagaDataInterface {};
            }

            public function onStarted(string $sagaId, SagaDataInterface $data): void {}

            public function onFinished(string $sagaId, SagaDataInterface $data): void {}

            public function onFailed(string $sagaId, SagaDataInterface $data): void {}

            private function step(): StepBuilder { return new StepBuilder(new SagaDefinitionBuilder()); }
        };

        $sagaFactory = new class($saga) implements SagaFactoryInterface
        {
            private $saga;

            public function __construct($saga) { $this->saga = $saga; }
            public function create(string $sagaType, SagaDataInterface $sagaInitialData = null): SagaInterface { return $this->saga; }
        };

        $messageProducer = new class($this->replyMessageProducer, $this->replyMessageFactory) implements MessageProducerInterface
        {
            /** @var ReplyMessageProducer */
            private $replyMessageProducer;
            /** @var ReplyMessageFactoryInterface */
            private $replyMessageFactory;

            public function __construct($replyMessageProducer, $replyMessageFactory) {
                $this->replyMessageProducer = $replyMessageProducer;
                $this->replyMessageFactory = $replyMessageFactory;
            }

            public function send(CommandMessage $message): void
            {
                $this->replyMessageProducer->send(('success' === $message->getCommandType())
                    ? $this->replyMessageFactory->makeSuccess($message->getSagaId(), $message->getId(), '{}')
                    : $this->replyMessageFactory->makeFailure($message->getSagaId(), $message->getId(), '{}')
                );
            }
        };

        $sagaCommandProducer = new SagaCommandProducer($messageProducer, $this->messageFactory, $this->payloadSerializer);
        $sagaActionsProcessor = new SagaActionsProcessor($this->instanceRepo, $this->sagaSerializer, $this->stateSerializer, $this->messageIdGenerator, $this->sagaLocker, $sagaCommandProducer);
        $sagaReplyHandler = new SagaReplyHandler($this->instanceRepo, $this->sagaSerializer, $this->stateSerializer, $sagaFactory, $sagaActionsProcessor);
        $this->replyMessageProducer->setSagaReplyHandler($sagaReplyHandler);

        $sagaCreator = new SagaCreator($this->instanceRepo, $this->instanceFactory, $this->sagaLocker, $sagaActionsProcessor);
        $sagaCreator->create($saga, $sagaData);
    }

    /**
     * @throws LocalCommandException
     */
    public function testLocalCompensationWorks()
    {
        $sagaData = new EmptySagaData();
        $detector = \Mockery::mock(CommandExecutionDetectorInterface::class);
        $detector->shouldReceive('execute')->once();

        $localCommand = new class($sagaData) implements LocalCommandInterface
        {
            private $sagaData;

            public function __construct($sagaData) { $this->sagaData = $sagaData; }

            public function getSagaDataType(): string { return get_class($this->sagaData); }
            public function execute(SagaDataInterface $sagaData): void {
                throw new LocalCommandException('command exception');
            }
        };

        $localCompensation = new class($detector) implements LocalCommandInterface
        {
            /** @var CommandExecutionDetectorInterface */
            private $detector;

            public function __construct($detector) { $this->detector = $detector; }

            public function getSagaDataType(): string { return EmptySagaData::class; }
            public function execute(SagaDataInterface $sagaData): void {
                $this->detector->execute();
            }
        };

        $saga = new class($localCommand, $localCompensation) implements SagaInterface
        {
            private $localCommand;
            private $localCompensation;

            public function __construct($localCommand, $localCompensation)
            {
                $this->localCommand = $localCommand;
                $this->localCompensation = $localCompensation;
            }

            public function getSagaDefinition(): SagaDefinition
            {
                $steps = $this
                    ->step()
                    ->withLocalCompensation($this->localCompensation)
                    ->step()
                    ->localCommand($this->localCommand);

                return $steps->build();
            }

            public function getSagaType(): string
            {
                return 'dummy';
            }

            public function getSagaInitialData(): ?SagaDataInterface
            {
                return new class implements SagaDataInterface {};
            }

            public function onStarted(string $sagaId, SagaDataInterface $data): void {}

            public function onFinished(string $sagaId, SagaDataInterface $data): void {}

            public function onFailed(string $sagaId, SagaDataInterface $data): void {}

            private function step(): StepBuilder { return new StepBuilder(new SagaDefinitionBuilder()); }
        };

        $sagaFactory = new class($saga) implements SagaFactoryInterface
        {
            private $saga;

            public function __construct($saga) { $this->saga = $saga; }
            public function create(string $sagaType, SagaDataInterface $sagaInitialData = null): SagaInterface { return $this->saga; }
        };

        $messageProducer = new class implements MessageProducerInterface
        {
            public function send(CommandMessage $message): void {}
        };

        $sagaCommandProducer = new SagaCommandProducer($messageProducer, $this->messageFactory, $this->payloadSerializer);
        $sagaActionsProcessor = new SagaActionsProcessor($this->instanceRepo, $this->sagaSerializer, $this->stateSerializer, $this->messageIdGenerator, $this->sagaLocker, $sagaCommandProducer);
        $sagaReplyHandler = new SagaReplyHandler($this->instanceRepo, $this->sagaSerializer, $this->stateSerializer, $sagaFactory, $sagaActionsProcessor);
        $this->replyMessageProducer->setSagaReplyHandler($sagaReplyHandler);

        $sagaCreator = new SagaCreator($this->instanceRepo, $this->instanceFactory, $this->sagaLocker, $sagaActionsProcessor);
        $sagaCreator->create($saga, $sagaData);
    }

    public function _after(FunctionalTester $I)
    {
        \Mockery::close();
        $this->instanceRepo->reset();
    }
}
