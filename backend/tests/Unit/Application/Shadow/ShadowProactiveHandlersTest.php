<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Shadow;

use App\Application\Shadow\Commands\AnswerShadowInterventionCommand;
use App\Application\Shadow\Commands\SkipShadowInterventionCommand;
use App\Application\Shadow\Commands\UpdateShadowInterventionPolicyCommand;
use App\Application\Shadow\CurrentSegmentResolver;
use App\Application\Shadow\Handlers\AnswerShadowInterventionHandler;
use App\Application\Shadow\Handlers\CheckShadowInterventionHandler;
use App\Application\Shadow\Handlers\SkipShadowInterventionHandler;
use App\Application\Shadow\Handlers\UpdateShadowInterventionPolicyHandler;
use App\Application\Shadow\Queries\CheckShadowInterventionQuery;
use App\Application\Shadow\ShadowChallengeGenerator;
use App\Application\Shadow\ShadowContextFactory;
use App\Application\Shadow\ShadowInterventionAnswerPromptBuilder;
use App\Application\Shadow\ShadowInterventionAnswerer;
use App\Application\Shadow\ShadowInterventionContextBuilder;
use App\Application\Shadow\ShadowInterventionDecider;
use App\Application\Shadow\ShadowInterventionPlanner;
use App\Application\Shadow\ShadowInterventionReasonBuilder;
use App\Application\Shadow\ShadowSessionResolver;
use App\Application\Shadow\TimelineContextBuilder;
use App\Domain\Shadow\ShadowConversationContextInterface;
use App\Domain\Shadow\ShadowIntervention;
use App\Domain\Shadow\ShadowInterventionId;
use App\Domain\Shadow\ShadowInterventionPolicy;
use App\Domain\Shadow\ShadowInterventionTrigger;
use App\Domain\Shadow\ShadowInterventionType;
use App\Domain\Shadow\ShadowSession;
use App\Domain\Shadow\ShadowSessionId;
use App\Domain\Shadow\ShadowTimestamp;
use App\Domain\Shadow\ShadowTutorMode;
use App\Domain\Speech\Transcript;
use App\Domain\Speech\TranscriptId;
use App\Domain\Speech\TranscriptLanguage;
use App\Domain\Speech\TranscriptRepositoryInterface;
use App\Domain\Speech\TranscriptSegment;
use App\Domain\Speech\TranscriptSegmentCollection;
use App\Domain\Translation\TranslationRepositoryInterface;
use App\Domain\Video\VideoId;
use App\Domain\Video\VideoRepositoryInterface;
use App\Domain\VideoIntelligence\VideoIntelligenceFactoryInterface;
use App\Infrastructure\Chat\MockChatProvider;
use App\Infrastructure\Shadow\InMemoryShadowSessionRepository;
use PHPUnit\Framework\TestCase;

final class ShadowProactiveHandlersTest extends TestCase
{
    private InMemoryShadowSessionRepository $sessionRepository;

    private VideoId $videoId;

    protected function setUp(): void
    {
        $this->sessionRepository = new InMemoryShadowSessionRepository();
        $this->videoId = VideoId::generate();
    }

    public function testInterventionCheckReturnsNoneWhenDisabled(): void
    {
        $session = $this->persistSession(
            ShadowSession::start(
                ShadowSessionId::generate(),
                $this->videoId,
                'fr',
            ),
        );

        $result = $this->checkHandler()->__invoke(new CheckShadowInterventionQuery(
            videoId: $this->videoId->value,
            sessionId: $session->id()->value,
            currentTimeSeconds: 2.5,
        ));

        self::assertFalse($result->hasIntervention);
    }

    public function testInterventionCheckRecommendsPauseWhenEnabled(): void
    {
        $session = $this->persistSession(
            ShadowSession::start(
                ShadowSessionId::generate(),
                $this->videoId,
                'fr',
            )->withInterventionPolicy(ShadowTutorMode::Gentle->toPolicy()),
        );

        $result = $this->checkHandler()->__invoke(new CheckShadowInterventionQuery(
            videoId: $this->videoId->value,
            sessionId: $session->id()->value,
            currentTimeSeconds: 2.5,
        ));

        self::assertTrue($result->hasIntervention);
        self::assertTrue($result->recommendPause);
        self::assertNotNull($result->intervention);
    }

    public function testAnswerFlowStoresHistoryAndReplies(): void
    {
        $intervention = ShadowIntervention::create(
            ShadowInterventionId::generate(),
            ShadowInterventionType::ConceptCheck,
            ShadowInterventionTrigger::RepeatedConcept,
            'Concept repeated.',
            ShadowTimestamp::fromSeconds(4.0),
            'Explain in your own words',
            allowAutoPause: true,
            challenge: \App\Domain\Shadow\ShadowChallenge::create('What is the main idea?'),
        );

        $session = $this->persistSession(
            ShadowSession::start(
                ShadowSessionId::generate(),
                $this->videoId,
                'fr',
            )
                ->withInterventionPolicy(ShadowTutorMode::Gentle->toPolicy())
                ->recordIntervention($intervention),
        );

        $result = $this->answerHandler()->__invoke(new AnswerShadowInterventionCommand(
            videoId: $this->videoId->value,
            sessionId: $session->id()->value,
            interventionId: $intervention->id()->value,
            answer: 'It introduces the main topic.',
            currentTimeSeconds: 4.0,
        ));

        self::assertStringContainsString('Mock answer', $result->reply);
        self::assertGreaterThanOrEqual(2, count($result->session->interactions));

        $stored = $this->sessionRepository->findById($session->id());
        self::assertTrue($stored?->interventions()->findById($intervention->id())?->isAnswered());
    }

    public function testSkipFlowMarksInterventionSkipped(): void
    {
        $intervention = ShadowIntervention::create(
            ShadowInterventionId::generate(),
            ShadowInterventionType::ReflectionPrompt,
            ShadowInterventionTrigger::LongSilence,
            'No interaction for a while.',
            ShadowTimestamp::fromSeconds(8.0),
            'Reflect on what you heard',
            allowAutoPause: false,
        );

        $session = $this->persistSession(
            ShadowSession::start(
                ShadowSessionId::generate(),
                $this->videoId,
                'fr',
            )->recordIntervention($intervention),
        );

        $result = $this->skipHandler()->__invoke(new SkipShadowInterventionCommand(
            videoId: $this->videoId->value,
            sessionId: $session->id()->value,
            interventionId: $intervention->id()->value,
            currentTimeSeconds: 8.0,
        ));

        self::assertFalse($result->hasIntervention);
        self::assertTrue(
            $this->sessionRepository->findById($session->id())
                ?->interventions()
                ->findById($intervention->id())
                ?->isSkipped(),
        );
    }

    public function testPolicyUpdatePersistsInSession(): void
    {
        $session = $this->persistSession(
            ShadowSession::start(
                ShadowSessionId::generate(),
                $this->videoId,
                'fr',
            ),
        );

        $policy = ShadowTutorMode::Normal->toPolicy()->withAutoResume(true);
        $result = $this->policyHandler()->__invoke(new UpdateShadowInterventionPolicyCommand(
            videoId: $this->videoId->value,
            sessionId: $session->id()->value,
            policy: $policy,
        ));

        self::assertTrue($result->enabled);
        self::assertTrue($result->autoResume);
        self::assertTrue($this->sessionRepository->findById($session->id())?->interventionPolicy()->autoResume());
    }

    private function persistSession(ShadowSession $session): ShadowSession
    {
        $this->sessionRepository->save($session);

        return $session;
    }

    private function checkHandler(): CheckShadowInterventionHandler
    {
        return new CheckShadowInterventionHandler(
            $this->sessionRepository,
            new ShadowSessionResolver($this->sessionRepository),
            $this->contextBuilder(),
            $this->planner(),
        );
    }

    private function answerHandler(): AnswerShadowInterventionHandler
    {
        return new AnswerShadowInterventionHandler(
            $this->sessionRepository,
            new ShadowSessionResolver($this->sessionRepository),
            $this->shadowContextFactory(),
            new ShadowInterventionAnswerer(
                new MockChatProvider(),
                new ShadowInterventionAnswerPromptBuilder(),
            ),
            new \App\Application\Shadow\ShadowAnswerLanguageResolver(),
        );
    }

    private function skipHandler(): SkipShadowInterventionHandler
    {
        return new SkipShadowInterventionHandler(
            $this->sessionRepository,
            new ShadowSessionResolver($this->sessionRepository),
        );
    }

    private function policyHandler(): UpdateShadowInterventionPolicyHandler
    {
        return new UpdateShadowInterventionPolicyHandler(
            $this->sessionRepository,
            new ShadowSessionResolver($this->sessionRepository),
        );
    }

    private function planner(): ShadowInterventionPlanner
    {
        return new ShadowInterventionPlanner(
            new ShadowInterventionDecider(
                new ShadowInterventionReasonBuilder(),
                new ShadowChallengeGenerator(),
            ),
        );
    }

    private function contextBuilder(): ShadowInterventionContextBuilder
    {
        $videoRepository = $this->createMock(VideoRepositoryInterface::class);
        $videoRepository->method('findById')->willReturn(null);
        $intelligenceFactory = $this->createMock(VideoIntelligenceFactoryInterface::class);

        return new ShadowInterventionContextBuilder(
            $this->shadowContextFactory(),
            $videoRepository,
            $intelligenceFactory,
        );
    }

    private function shadowContextFactory(): ShadowContextFactory
    {
        $conversationContext = $this->createMock(ShadowConversationContextInterface::class);
        $conversationContext->method('loadRecentMessages')->willReturn([]);

        return new ShadowContextFactory(
            $this->transcriptRepository(),
            $this->translationRepository(),
            new CurrentSegmentResolver(),
            new TimelineContextBuilder(new CurrentSegmentResolver()),
            $this->sessionRepository,
            $conversationContext,
        );
    }

    private function transcriptRepository(): TranscriptRepositoryInterface
    {
        $repository = $this->createMock(TranscriptRepositoryInterface::class);
        $repository
            ->method('findByVideoId')
            ->willReturn(Transcript::create(
                TranscriptId::generate(),
                TranscriptLanguage::English,
                new TranscriptSegmentCollection([
                    TranscriptSegment::create(0, 0.0, 5.0, 'Bonjour tout le monde'),
                    TranscriptSegment::create(1, 5.0, 10.0, 'antidisestablishmentarianism debate'),
                ]),
            ));

        return $repository;
    }

    private function translationRepository(): TranslationRepositoryInterface
    {
        $repository = $this->createMock(TranslationRepositoryInterface::class);
        $repository->method('findByVideoIdAndLanguage')->willReturn(null);

        return $repository;
    }
}
