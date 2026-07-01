<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Learning;

use App\Application\Learning\DTO\LearningAdaptiveHints;
use App\Application\Learning\LearningAdaptiveAdvisor;
use App\Application\Learning\LearningAdaptiveShadowPolicyResolver;
use App\Application\Learning\LearningAdaptiveVoiceResolver;
use App\Application\Learning\LearningInsightGenerator;
use App\Application\Learning\LearningProfileBuilder;
use App\Application\Learning\LearningRecommendationEngine;
use App\Application\Learning\LearningSignalCollector;
use App\Application\Learning\QualityLearningSignalMapper;
use App\Application\Learning\ReviewLearningSignalMapper;
use App\Application\Learning\ShadowLearningSignalMapper;
use App\Application\Learning\TelemetryLearningSignalMapper;
use App\Application\Shadow\DTO\ShadowAnswerVoiceMetadata;
use App\Application\Shadow\ShadowWatchPromptBuilder;
use App\Domain\Shadow\ShadowChallengeLevel;
use App\Domain\Shadow\ShadowTutorMode;
use App\Domain\Shadow\ShadowVoiceLanguage;
use App\Domain\Shadow\ShadowVoiceMode;
use App\Domain\Shadow\ShadowVoicePreference;
use App\Infrastructure\Learning\InMemoryLearningProfileRepository;
use App\Infrastructure\Orchestrator\LearningAwarePipelinePlanner;
use PHPUnit\Framework\TestCase;

final class LearningIntegrationTest extends TestCase
{
    private InMemoryLearningProfileRepository $repository;
    private LearningProfileBuilder $builder;
    private LearningAdaptiveAdvisor $advisor;

    protected function setUp(): void
    {
        $this->repository = new InMemoryLearningProfileRepository();
        $this->builder = new LearningProfileBuilder(
            $this->repository,
            new LearningSignalCollector(
                new ShadowLearningSignalMapper(),
                new ReviewLearningSignalMapper(),
                new TelemetryLearningSignalMapper(),
                new QualityLearningSignalMapper(),
            ),
            new LearningInsightGenerator(),
            new LearningRecommendationEngine(),
        );
        $this->advisor = new LearningAdaptiveAdvisor($this->repository);
    }

    public function testShadowUsesExplanationStylePreference(): void
    {
        $this->seedAdaptiveProfile([
            ['source' => 'shadow', 'event' => 'explanation_depth_preference', 'depth' => 'short'],
            ['source' => 'shadow', 'event' => 'explanation_depth_preference', 'depth' => 'short'],
            ['source' => 'shadow', 'event' => 'explanation_depth_preference', 'depth' => 'short'],
        ]);

        $prompt = (new ShadowWatchPromptBuilder())->build(
            $this->watchContext(),
            \App\Domain\Shadow\ShadowQuestion::fromString('What does this mean?'),
            ShadowVoiceLanguage::English,
            $this->advisor->hints()->explanationStyle,
        );

        self::assertStringContainsString('concise', $prompt->value());
    }

    public function testVoicePreferenceAppliedWhenAdaptiveEnabled(): void
    {
        $this->seedAdaptiveProfile([
            ['source' => 'shadow', 'event' => 'voice_language_preference', 'language' => 'fr'],
            ['source' => 'shadow', 'event' => 'voice_language_preference', 'language' => 'fr'],
            ['source' => 'shadow', 'event' => 'voice_language_preference', 'language' => 'fr'],
        ]);

        $voice = new LearningAdaptiveVoiceResolver()->apply(
            new ShadowAnswerVoiceMetadata(
                ShadowVoiceLanguage::English,
                ShadowVoiceLanguage::English,
                false,
                'target_language',
            ),
            ShadowVoicePreference::default(),
            $this->advisor->hints(),
            false,
        );

        self::assertSame(ShadowVoiceLanguage::French, $voice->answerLanguage);
        self::assertSame('adaptive_voice_preference', $voice->reason);
    }

    public function testManualVoiceOverrideWins(): void
    {
        $this->seedAdaptiveProfile([
            ['source' => 'shadow', 'event' => 'voice_language_preference', 'language' => 'fr'],
            ['source' => 'shadow', 'event' => 'voice_language_preference', 'language' => 'fr'],
            ['source' => 'shadow', 'event' => 'voice_language_preference', 'language' => 'fr'],
        ]);

        $manualPreference = ShadowVoicePreference::default()->withMode(
            ShadowVoiceMode::Manual,
            ShadowVoiceLanguage::German,
        );

        $voice = new LearningAdaptiveVoiceResolver()->apply(
            new ShadowAnswerVoiceMetadata(
                ShadowVoiceLanguage::German,
                ShadowVoiceLanguage::German,
                false,
                'manual_selection',
            ),
            $manualPreference,
            $this->advisor->hints(),
            false,
        );

        self::assertSame(ShadowVoiceLanguage::German, $voice->answerLanguage);
        self::assertSame('manual_selection', $voice->reason);
    }

    public function testChallengeLevelAdaptedForProactiveTutor(): void
    {
        $this->seedAdaptiveProfile([
            ['source' => 'shadow', 'event' => 'intervention_skipped', 'difficulty' => 'hard'],
            ['source' => 'shadow', 'event' => 'intervention_skipped', 'difficulty' => 'hard'],
            ['source' => 'shadow', 'event' => 'intervention_skipped', 'difficulty' => 'hard'],
        ]);

        $policy = (new LearningAdaptiveShadowPolicyResolver())->apply(
            ShadowTutorMode::Normal->toPolicy(),
            $this->advisor->hints(),
        );

        self::assertSame(ShadowChallengeLevel::Easy, $policy->challengeLevel());
    }

    public function testDisabledAdaptiveModeLeavesPolicyUnchanged(): void
    {
        $this->builder->recordPayload('default', [
            'source' => 'shadow',
            'event' => 'intervention_skipped',
            'difficulty' => 'hard',
        ]);

        $base = ShadowTutorMode::Normal->toPolicy();
        $policy = (new LearningAdaptiveShadowPolicyResolver())->apply(
            $base,
            $this->advisor->hints(),
        );

        self::assertSame($base->challengeLevel(), $policy->challengeLevel());
    }

    public function testNoProfileFallbackIsInactive(): void
    {
        self::assertFalse($this->advisor->hints()->active);
    }

    public function testAiDirectorReceivesProviderSoftPreference(): void
    {
        $this->seedAdaptiveProfile([
            [
                'source' => 'telemetry',
                'providerId' => 'ollama',
                'stage' => 'translation',
                'qualityScore' => 95,
                'success' => true,
            ],
            [
                'source' => 'telemetry',
                'providerId' => 'ollama',
                'stage' => 'translation',
                'qualityScore' => 93,
                'success' => true,
            ],
        ]);

        $inner = $this->createMock(\App\Domain\Orchestrator\PipelinePlannerInterface::class);
        $registry = (new \App\Infrastructure\AI\AIEngineRegistryFactory())->create();

        $recommendation = \App\Domain\Orchestrator\PipelineRecommendation::create(
            \App\Domain\Orchestrator\PipelineRecommendationId::generate(),
            \App\Domain\Orchestrator\ProcessingStrategy::Balanced,
            \App\Domain\Pipeline\PipelineConfiguration::create(
                \App\Domain\Pipeline\PipelineConfigurationId::generate(),
                [
                    \App\Domain\Pipeline\PipelineStage::create(
                        \App\Domain\Pipeline\PipelineStageType::SpeechToText,
                        'faster_whisper',
                    ),
                    \App\Domain\Pipeline\PipelineStage::create(
                        \App\Domain\Pipeline\PipelineStageType::Translation,
                        'faster_whisper',
                    ),
                    \App\Domain\Pipeline\PipelineStage::create(
                        \App\Domain\Pipeline\PipelineStageType::TextToSpeech,
                        'f5_tts',
                    ),
                    \App\Domain\Pipeline\PipelineStage::create(
                        \App\Domain\Pipeline\PipelineStageType::VoiceClone,
                        'openvoice',
                    ),
                    \App\Domain\Pipeline\PipelineStage::create(
                        \App\Domain\Pipeline\PipelineStageType::LipSync,
                        'latentsync',
                    ),
                    \App\Domain\Pipeline\PipelineStage::create(
                        \App\Domain\Pipeline\PipelineStageType::VideoRender,
                        'ffmpeg',
                    ),
                ],
            ),
            'Base recommendation',
            120,
            4,
            8.0,
            ['Base reason'],
        );

        $inner->method('recommend')->willReturn($recommendation);

        $planner = new LearningAwarePipelinePlanner($inner, $this->advisor, $registry);
        $result = $planner->recommend($this->sampleVideoIntelligence());

        self::assertSame('ollama', $result->pipelineConfiguration()->stages()->findByType(
            \App\Domain\Pipeline\PipelineStageType::Translation,
        )?->providerId());
        self::assertTrue(
            in_array(
                'Adaptive learning applied soft provider preference "ollama" for translation.',
                $result->reasons(),
                true,
            ),
        );
    }

    /**
     * @param list<array<string, mixed>> $payloads
     */
    private function seedAdaptiveProfile(array $payloads): void
    {
        $profile = $this->builder->getOrCreate('default')->enableAdaptiveRecommendations();
        $this->repository->save($profile);

        foreach ($payloads as $payload) {
            $this->builder->recordPayload('default', $payload);
        }
    }

    private function sampleVideoIntelligence(): \App\Domain\VideoIntelligence\VideoIntelligence
    {
        return \App\Domain\VideoIntelligence\VideoIntelligence::create(
            \App\Domain\VideoIntelligence\VideoIntelligenceId::generate(),
            120.0,
            \App\Domain\VideoIntelligence\VideoScene::Interview,
            \App\Domain\VideoIntelligence\AudioCharacteristics::create(
                'english',
                1,
                \App\Domain\VideoIntelligence\AudioNoiseLevel::Low,
                \App\Domain\VideoIntelligence\BackgroundMusic::NotDetected,
                \App\Domain\VideoIntelligence\SpeechSpeed::Normal,
                \App\Domain\VideoIntelligence\SpeechConfidence::create(90),
            ),
            \App\Domain\VideoIntelligence\VisualCharacteristics::create(
                '1920x1080',
                30.0,
                \App\Domain\VideoIntelligence\LightingCondition::Good,
                \App\Domain\VideoIntelligence\LipVisibility::Excellent,
                1,
            ),
            \App\Domain\VideoIntelligence\SpeechCharacteristics::create(
                \App\Domain\VideoIntelligence\VideoEmotion::Neutral,
                140.0,
                5,
                false,
            ),
            \App\Domain\VideoIntelligence\VideoSpeakerCollection::empty(),
            true,
            8.0,
        );
    }

    private function watchContext(): \App\Application\Shadow\WatchContext
    {
        return new \App\Application\Shadow\WatchContext(
            videoId: '00000000-0000-4000-8000-000000000001',
            currentTimeSeconds: 2.5,
            targetLanguage: 'fr',
            conversationId: null,
            currentTranscriptSegment: null,
            currentTranslationSegment: null,
            previousTranscriptSegment: null,
            nextTranscriptSegment: null,
            previousTranslationSegment: null,
            nextTranslationSegment: null,
            nearbyTranscriptContext: '',
            nearbyTranslationContext: '',
            currentSpeaker: null,
            recentInteractions: [],
            conversationMemory: [],
        );
    }
}
