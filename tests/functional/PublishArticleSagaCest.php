<?php

namespace PhpSagas\Orchestrator\Tests;

use Mockery\MockInterface;
use PhpSagas\Orchestrator\InstantiationEngine\DefaultSagaInstanceFactory;
use PhpSagas\Orchestrator\ExecutionEngine\NullSagaLocker;
use PhpSagas\Orchestrator\ExecutionEngine\SagaActionsProcessor;
use PhpSagas\Orchestrator\ExecutionEngine\SagaCommandProducerInterface;
use PhpSagas\Orchestrator\ExecutionEngine\SagaCreator;
use PhpSagas\Orchestrator\ExecutionEngine\SagaExecutionStateSerializer;
use PhpSagas\Orchestrator\ExecutionEngine\SagaStatusEnum;
use PhpSagas\Orchestrator\Tests\_support\Implementation\InMemoryMessageIdGenerator;
use PhpSagas\Orchestrator\Tests\_support\Implementation\InMemorySagaInstanceRepository;
use PhpSagas\Orchestrator\Tests\_support\Implementation\JsonEncodeSagaSerializer;
use PhpSagas\Orchestrator\Tests\_support\Implementation\PublishArticleSaga\Article;
use PhpSagas\Orchestrator\Tests\_support\Implementation\PublishArticleSaga\ArticleRepository;
use PhpSagas\Orchestrator\Tests\_support\Implementation\PublishArticleSaga\CheckPlagiarismCommand;
use PhpSagas\Orchestrator\Tests\_support\Implementation\PublishArticleSaga\DummyInitialData;
use PhpSagas\Orchestrator\Tests\_support\Implementation\PublishArticleSaga\PublishArticleSaga;
use PhpSagas\Orchestrator\Tests\_support\Implementation\PublishArticleSaga\PublishArticleSagaData;
use PhpSagas\Orchestrator\Tests\_support\Implementation\SagaExecutionDetectorInterface;

/**
 * @author Oleg Filatov <phpsagas@gmail.com>
 */
class PublishArticleSagaCest
{
    /** @var InMemorySagaInstanceRepository */
    private $sagaInstanceRepo;

    public function _before(FunctionalTester $I)
    {
        $this->sagaInstanceRepo = new InMemorySagaInstanceRepository();
    }

    /**
     * @param FunctionalTester $I
     *
     * @throws \Exception
     */
    public function testLocalSagaSuccessfulWorks(FunctionalTester $I): void
    {
        $sagaData = new PublishArticleSagaData('dummy article title', 'dummy article body');
        $sagaInitialData = new DummyInitialData();

        $sagaSerializer = new JsonEncodeSagaSerializer();
        $factory = new DefaultSagaInstanceFactory($sagaSerializer);
        $executionStateSerializer = new SagaExecutionStateSerializer();
        $checkPlagiarismCommand = new CheckPlagiarismCommand(false);
        $executionDetector = \Mockery::mock(SagaExecutionDetectorInterface::class);
        $executionDetector->shouldReceive('starting')->once();
        $executionDetector->shouldReceive('finished')->once();
        $executionDetector->shouldReceive('failed')->never();

        /** @var SagaCommandProducerInterface|MockInterface $producer */
        $producer = \Mockery::mock(SagaCommandProducerInterface::class);
        $producer->shouldReceive('send')->never();

        $messageIdGenerator = new InMemoryMessageIdGenerator();
        $sagaLocker = new NullSagaLocker();
        $processor = new SagaActionsProcessor($this->sagaInstanceRepo, $sagaSerializer, $executionStateSerializer, $messageIdGenerator, $sagaLocker, $producer);
        $sagaCreator = new SagaCreator($this->sagaInstanceRepo, $factory, $sagaLocker, $processor);

        $saga = new PublishArticleSaga($checkPlagiarismCommand, $sagaInitialData, $executionDetector);
        $sagaInstance = $sagaCreator->create($saga, $sagaData);

        $I->assertEquals('publish_article_saga', $sagaInstance->getSagaType());
        $I->assertNotNull($sagaInstance->getSagaId());

        $article = ArticleRepository::getInstance()->findByTitle($sagaData->getArticleTitle());
        $I->assertNotNull($article);
        $I->assertEquals(Article::STATUS_APPROVED, $article->getStatus());

        $sagaInstance = $this->sagaInstanceRepo->findSaga($sagaInstance->getSagaId());
        $I->assertEquals(SagaStatusEnum::FINISHED, $sagaInstance->getStatus());
    }

    /**
     * @param FunctionalTester $I
     *
     * @throws \Exception
     */
    public function testLocalSagaRolledBackWorks(FunctionalTester $I): void
    {
        $sagaData = new PublishArticleSagaData('dummy article title', 'dummy article body');
        $sagaInitialData = new DummyInitialData();

        $sagaSerializer = new JsonEncodeSagaSerializer();
        $factory = new DefaultSagaInstanceFactory($sagaSerializer);
        $executionStateSerializer = new SagaExecutionStateSerializer();
        $checkPlagiarismCommand = new CheckPlagiarismCommand(true);
        $executionDetector = \Mockery::mock(SagaExecutionDetectorInterface::class);
        $executionDetector->shouldReceive('starting')->once();
        $executionDetector->shouldReceive('failed')->once();
        $executionDetector->shouldReceive('finished')->never();

        /** @var SagaCommandProducerInterface|MockInterface $producer */
        $producer = \Mockery::mock(SagaCommandProducerInterface::class);
        $producer->shouldReceive('send')->never();

        $messageIdGenerator = new InMemoryMessageIdGenerator();
        $sagaLocker = new NullSagaLocker();
        $processor = new SagaActionsProcessor($this->sagaInstanceRepo, $sagaSerializer, $executionStateSerializer, $messageIdGenerator, $sagaLocker, $producer);
        $sagaCreator = new SagaCreator($this->sagaInstanceRepo, $factory, $sagaLocker, $processor);

        $saga = new PublishArticleSaga($checkPlagiarismCommand, $sagaInitialData, $executionDetector);
        $sagaInstance = $sagaCreator->create($saga, $sagaData);

        $I->assertEquals('publish_article_saga', $sagaInstance->getSagaType());
        $I->assertNotNull($sagaInstance->getSagaId());

        $article = ArticleRepository::getInstance()->findByTitle($sagaData->getArticleTitle());
        $I->assertNotNull($article);
        $I->assertEquals(Article::STATUS_REJECTED, $article->getStatus());

        $sagaInstance = $this->sagaInstanceRepo->findSaga($sagaInstance->getSagaId());
        $I->assertEquals(SagaStatusEnum::FAILED, $sagaInstance->getStatus());
    }

    public function _after(FunctionalTester $I)
    {
        \Mockery::close();
        ArticleRepository::getInstance()->reset();
        $this->sagaInstanceRepo->reset();
    }
}
