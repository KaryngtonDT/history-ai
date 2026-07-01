<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Shadow;

use App\Application\Shadow\ShadowChallengeGenerator;
use App\Application\Shadow\ShadowInterventionContext;
use App\Application\Shadow\ShadowInterventionDecider;
use App\Application\Shadow\ShadowInterventionPlanner;
use App\Application\Shadow\ShadowInterventionReasonBuilder;
use App\Application\Shadow\WatchContext;
use App\Application\Shadow\WatchContextSegment;
use App\Domain\Review\LipSyncStrengthPreference;
use App\Domain\Review\RenderingPresetPreference;
use App\Domain\Review\TranslationStylePreference;
use App\Domain\Review\UserPreferenceProfile;
use App\Domain\Review\VoiceStabilityPreference;
use App\Domain\Shadow\ShadowIntervention;
use App\Domain\Shadow\ShadowInterventionCollection;
use App\Domain\Shadow\ShadowInterventionPolicy;
use App\Domain\Shadow\ShadowInterventionTrigger;
use App\Domain\Shadow\ShadowInterventionType;
use App\Domain\Shadow\ShadowInteraction;
use App\Domain\Shadow\ShadowPlaybackState;
use App\Domain\Shadow\ShadowSession;
use App\Domain\Shadow\ShadowSessionId;
use App\Domain\Shadow\ShadowTimestamp;
use App\Domain\Video\VideoId;
use App\Domain\VideoIntelligence\AudioCharacteristics;
use App\Domain\VideoIntelligence\AudioNoiseLevel;
use App\Domain\VideoIntelligence\BackgroundMusic;
use App\Domain\VideoIntelligence\LightingCondition;
use App\Domain\VideoIntelligence\LipVisibility;
use App\Domain\VideoIntelligence\SpeechCharacteristics;
use App\Domain\VideoIntelligence\SpeechConfidence;
use App\Domain\VideoIntelligence\SpeechSpeed;
use App\Domain\VideoIntelligence\VideoEmotion;
use App\Domain\VideoIntelligence\VideoIntelligence;
use App\Domain\VideoIntelligence\VideoIntelligenceId;
use App\Domain\VideoIntelligence\VideoScene;
use App\Domain\VideoIntelligence\VideoSpeakerCollection;
use App\Domain\VideoIntelligence\VisualCharacteristics;
use PHPUnit\Framework\TestCase;

final class ShadowInterventionEngineTest extends TestCase
{
    private ShadowInterventionPlanner $planner;

    protected function setUp(): void
    {
        $decider = new ShadowInterventionDecider(
            new ShadowInterventionReasonBuilder(),
            new ShadowChallengeGenerator(),
        );
        $this->planner = new ShadowInterventionPlanner($decider);
    }

    public function testNoInterventionWhenPolicyDisabled(): void
    {
        $context = $this->context(
            transcript: 'supercalifragilisticexpialidocious moment',
            policy: ShadowInterventionPolicy::disabled(),
        );

        self::assertNull($this->planner->plan($context));
    }

    public function testThrottlePreventsInterventionsTooCloseTogether(): void
    {
        $recent = ShadowInterventionCollection::empty()->append(
            ShadowIntervention::create(
                \App\Domain\Shadow\ShadowInterventionId::generate(),
                ShadowInterventionType::ReflectionPrompt,
                ShadowInterventionTrigger::LongSilence,
                'Earlier reflection.',
                ShadowTimestamp::fromSeconds(50.0),
                'Reflect',
                allowAutoPause: false,
            ),
        );

        $context = $this->context(
            time: 60.0,
            transcript: 'supercalifragilisticexpialidocious moment',
            recentInterventions: $recent,
        );

        self::assertNull($this->planner->plan($context));
    }

    public function testVocabularyTriggerCreatesChallenge(): void
    {
        $context = $this->context(
            transcript: 'The antidisestablishmentarianism debate continues.',
        );

        $intervention = $this->planner->plan($context);

        self::assertNotNull($intervention);
        self::assertSame(ShadowInterventionType::VocabularyCheck, $intervention->type());
        self::assertSame(
            ShadowInterventionTrigger::UnknownVocabulary,
            $intervention->trigger(),
        );
        self::assertNotNull($intervention->challenge());
        self::assertStringContainsString('antidisestablishmentarianism', $intervention->reason());
    }

    public function testLowConfidenceTranslationTriggerCreatesExplanation(): void
    {
        $context = $this->context(
            transcript: 'Bonjour tout le monde',
            translation: 'Bonjour tout le monde',
            targetLanguage: 'fr',
        );

        $intervention = $this->planner->plan($context);

        self::assertNotNull($intervention);
        self::assertSame(ShadowInterventionType::Explanation, $intervention->type());
        self::assertSame(
            ShadowInterventionTrigger::LowConfidenceTranslation,
            $intervention->trigger(),
        );
        self::assertNotNull($intervention->explanation());
    }

    public function testTopicShiftTriggerCreatesSummaryPrompt(): void
    {
        $context = $this->context(
            transcript: 'Economics drives inflation trends.',
            previousTranscript: 'History shapes cultural memory.',
        );

        $intervention = $this->planner->plan($context);

        self::assertNotNull($intervention);
        self::assertSame(ShadowInterventionType::SummaryPrompt, $intervention->type());
        self::assertSame(ShadowInterventionTrigger::TopicShift, $intervention->trigger());
    }

    public function testRepeatedPausesTriggerConceptCheck(): void
    {
        $session = ShadowSession::start(
            ShadowSessionId::generate(),
            new VideoId('550e8400-e29b-41d4-a716-446655440001'),
            'fr',
        )->withTimestamp(ShadowTimestamp::fromSeconds(30.0))
            ->pause()
            ->resume()
            ->withTimestamp(ShadowTimestamp::fromSeconds(35.0))
            ->pause()
            ->resume();

        $context = $this->context(
            time: 40.0,
            transcript: 'A normal sentence here.',
            session: $session,
        );

        $intervention = $this->planner->plan($context);

        self::assertNotNull($intervention);
        self::assertSame(ShadowInterventionType::ConceptCheck, $intervention->type());
        self::assertSame(ShadowInterventionTrigger::RepeatedConcept, $intervention->trigger());
    }

    public function testNoInterventionWhilePaused(): void
    {
        $session = ShadowSession::start(
            ShadowSessionId::generate(),
            new VideoId('550e8400-e29b-41d4-a716-446655440001'),
            'fr',
        )->withTimestamp(ShadowTimestamp::fromSeconds(10.0))->pause();

        $context = $this->context(
            transcript: 'supercalifragilisticexpialidocious moment',
            session: $session,
        );

        self::assertSame(ShadowPlaybackState::Paused, $session->playbackState());
        self::assertNull($this->planner->plan($context));
    }

    public function testChallengeGenerationRespectsLevel(): void
    {
        $generator = new ShadowChallengeGenerator();
        $context = $this->context(transcript: 'antidisestablishmentarianism debate');

        $easy = $generator->generate(
            ShadowInterventionType::VocabularyCheck,
            \App\Domain\Shadow\ShadowChallengeLevel::Easy,
            \App\Domain\Shadow\ShadowExplanationStyle::Short,
            $context,
            'antidisestablishmentarianism',
        );

        self::assertNotNull($easy);
        self::assertStringContainsString('mean here', $easy->questionText());
    }

    public function testReasonIncludesPreferenceHint(): void
    {
        $builder = new ShadowInterventionReasonBuilder();
        $profile = UserPreferenceProfile::create(
            TranslationStylePreference::Literal,
            VoiceStabilityPreference::Medium,
            RenderingPresetPreference::Balanced,
            LipSyncStrengthPreference::Moderate,
        );
        $context = $this->context(
            transcript: 'short text',
            userPreferenceProfile: $profile,
        );

        $reason = $builder->build(
            ShadowInterventionTrigger::TopicShift,
            $context,
        );

        self::assertStringContainsString('literal', strtolower($reason));
    }

    public function testImportantSegmentUsesVideoIntelligence(): void
    {
        $context = $this->context(
            time: 90.0,
            transcript: 'A calm statement.',
            translation: 'Une déclaration calme.',
            videoIntelligence: $this->lowConfidenceIntelligence(),
        );

        $intervention = $this->planner->plan($context);

        self::assertNotNull($intervention);
        self::assertSame(ShadowInterventionTrigger::ImportantSegment, $intervention->trigger());
    }

    private function context(
        float $time = 12.0,
        string $transcript = 'Hello world',
        ?string $translation = 'Bonjour le monde',
        ?string $previousTranscript = null,
        string $targetLanguage = 'fr',
        ?ShadowSession $session = null,
        ?ShadowInterventionPolicy $policy = null,
        ?ShadowInterventionCollection $recentInterventions = null,
        ?VideoIntelligence $videoIntelligence = null,
        ?UserPreferenceProfile $userPreferenceProfile = null,
    ): ShadowInterventionContext {
        $session ??= ShadowSession::start(
            ShadowSessionId::generate(),
            new VideoId('550e8400-e29b-41d4-a716-446655440001'),
            $targetLanguage,
        )->withTimestamp(ShadowTimestamp::fromSeconds($time));

        $current = new WatchContextSegment(1, $time - 1, $time + 1, $transcript, $translation);
        $previous = null !== $previousTranscript
            ? new WatchContextSegment(0, $time - 3, $time - 1, $previousTranscript, null)
            : null;

        $watchContext = new WatchContext(
            videoId: '550e8400-e29b-41d4-a716-446655440001',
            currentTimeSeconds: $time,
            targetLanguage: $targetLanguage,
            conversationId: null,
            currentTranscriptSegment: $current,
            currentTranslationSegment: $current,
            previousTranscriptSegment: $previous,
            nextTranscriptSegment: null,
            previousTranslationSegment: null,
            nextTranslationSegment: null,
            nearbyTranscriptContext: $transcript,
            nearbyTranslationContext: $translation ?? '',
            currentSpeaker: null,
            recentInteractions: [],
            conversationMemory: [],
        );

        return new ShadowInterventionContext(
            $watchContext,
            $session,
            $policy ?? ShadowInterventionPolicy::gentleDefault(),
            $recentInterventions ?? ShadowInterventionCollection::empty(),
            $videoIntelligence,
            $userPreferenceProfile,
        );
    }

    private function lowConfidenceIntelligence(): VideoIntelligence
    {
        return VideoIntelligence::create(
            VideoIntelligenceId::generate(),
            300.0,
            VideoScene::Lecture,
            AudioCharacteristics::create(
                'english',
                1,
                AudioNoiseLevel::Low,
                BackgroundMusic::NotDetected,
                SpeechSpeed::Normal,
                SpeechConfidence::create(55),
            ),
            VisualCharacteristics::create('1920x1080', 30.0, LightingCondition::Good, LipVisibility::Excellent, 1),
            SpeechCharacteristics::create(VideoEmotion::Neutral, 140.0, 5, false),
            VideoSpeakerCollection::empty(),
            true,
            8.0,
        );
    }
}
