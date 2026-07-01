<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Learning;

use App\Application\Learning\LearningInsightGenerator;
use App\Application\Learning\LearningProfileBuilder;
use App\Application\Learning\LearningProfileJsonMapper;
use App\Application\Learning\LearningRecommendationEngine;
use App\Application\Learning\LearningSignalCollector;
use App\Application\Learning\QualityLearningSignalMapper;
use App\Application\Learning\ReviewLearningSignalMapper;
use App\Application\Learning\ShadowLearningSignalMapper;
use App\Application\Learning\TelemetryLearningSignalMapper;
use App\Domain\Learning\LearningInsightType;
use App\Domain\Learning\LearningRecommendationType;
use App\Infrastructure\Learning\InMemoryLearningProfileRepository;
use PHPUnit\Framework\TestCase;

final class LearningInsightEngineTest extends TestCase
{
    private InMemoryLearningProfileRepository $repository;
    private LearningProfileBuilder $builder;

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
    }

    public function testVocabularyGapGenerated(): void
    {
        for ($index = 0; $index < 3; ++$index) {
            $this->builder->recordPayload('default', [
                'source' => 'shadow',
                'event' => 'question_asked',
                'questionType' => 'vocabulary',
                'term' => 'epistemology',
            ]);
        }

        $profile = $this->repository->findByScope('default');

        self::assertNotNull($profile);
        self::assertSame(1, $profile->insights()->ofType(LearningInsightType::VocabularyGap)->count());
        self::assertNotEmpty($profile->insights()->ofType(LearningInsightType::VocabularyGap)->all()[0]->sourceSignalIds());
    }

    public function testChallengeLevelDecreaseRecommendation(): void
    {
        $profile = $this->builder->getOrCreate('default')->enableAdaptiveRecommendations();
        $this->repository->save($profile);

        for ($index = 0; $index < 3; ++$index) {
            $this->builder->recordPayload('default', [
                'source' => 'shadow',
                'event' => 'intervention_skipped',
                'difficulty' => 'hard',
            ]);
        }

        $profile = $this->repository->findByScope('default');

        self::assertNotNull($profile);
        self::assertSame(1, $profile->insights()->ofType(LearningInsightType::PreferredChallengeLevel)->count());
        self::assertSame(
            1,
            $profile->recommendations()->ofType(LearningRecommendationType::DecreaseChallengeLevel)->count(),
        );
    }

    public function testTranslationStylePreference(): void
    {
        $profile = $this->builder->getOrCreate('default')->enableAdaptiveRecommendations();
        $this->repository->save($profile);

        for ($index = 0; $index < 3; ++$index) {
            $this->builder->recordPayload('default', [
                'source' => 'shadow',
                'event' => 'translation_style_preference',
                'style' => 'literal',
            ]);
        }

        $profile = $this->repository->findByScope('default');

        self::assertNotNull($profile);
        self::assertSame(1, $profile->insights()->ofType(LearningInsightType::PreferredTranslationStyle)->count());
        self::assertSame(
            1,
            $profile->recommendations()->ofType(LearningRecommendationType::UseLiteralTranslation)->count(),
        );
    }

    public function testVoiceLanguagePreference(): void
    {
        $profile = $this->builder->getOrCreate('default')->enableAdaptiveRecommendations();
        $this->repository->save($profile);

        for ($index = 0; $index < 3; ++$index) {
            $this->builder->recordPayload('default', [
                'source' => 'shadow',
                'event' => 'voice_language_preference',
                'language' => 'fr',
            ]);
        }

        $profile = $this->repository->findByScope('default');

        self::assertNotNull($profile);
        self::assertSame(1, $profile->insights()->ofType(LearningInsightType::PreferredVoiceLanguage)->count());
        self::assertSame(
            1,
            $profile->recommendations()->ofType(LearningRecommendationType::PreferVoiceLanguage)->count(),
        );
        self::assertStringContainsString('fr', $profile->recommendations()->all()[0]->explanation());
    }

    public function testProviderPreference(): void
    {
        $profile = $this->builder->getOrCreate('default')->enableAdaptiveRecommendations();
        $this->repository->save($profile);

        foreach ([92, 94] as $score) {
            $this->builder->recordPayload('default', [
                'source' => 'telemetry',
                'providerId' => 'gemini',
                'stage' => 'translation',
                'qualityScore' => $score,
                'success' => true,
            ]);
        }

        $this->builder->recordPayload('default', [
            'source' => 'telemetry',
            'providerId' => 'ollama',
            'stage' => 'translation',
            'qualityScore' => 70,
            'success' => false,
        ]);

        $profile = $this->repository->findByScope('default');

        self::assertNotNull($profile);
        self::assertSame(1, $profile->insights()->ofType(LearningInsightType::ProviderPreference)->count());
        self::assertSame(
            1,
            $profile->recommendations()->ofType(LearningRecommendationType::PreferProvider)->count(),
        );
        self::assertStringContainsString('gemini', $profile->insights()->all()[0]->summary());
    }

    public function testResetProfileClearsDerivedState(): void
    {
        for ($index = 0; $index < 3; ++$index) {
            $this->builder->recordPayload('default', [
                'source' => 'shadow',
                'event' => 'repeated_vocabulary',
                'term' => 'ontology',
            ]);
        }

        $profile = $this->builder->reset('default');

        self::assertSame(0, $profile->signals()->count());
        self::assertSame(0, $profile->insights()->count());
        self::assertSame(0, $profile->recommendations()->count());
    }

    public function testDisabledAdaptiveRecommendationsProduceNoRecommendations(): void
    {
        for ($index = 0; $index < 3; ++$index) {
            $this->builder->recordPayload('default', [
                'source' => 'shadow',
                'event' => 'voice_language_preference',
                'language' => 'de',
            ]);
        }

        $profile = $this->repository->findByScope('default');

        self::assertNotNull($profile);
        self::assertFalse($profile->adaptiveRecommendationsEnabled());
        self::assertSame(1, $profile->insights()->count());
        self::assertSame(0, $profile->recommendations()->count());
    }

    public function testProfileJsonMapperRoundTrip(): void
    {
        $profile = $this->builder->getOrCreate('default')->enableAdaptiveRecommendations();
        $this->repository->save($profile);

        for ($index = 0; $index < 3; ++$index) {
            $this->builder->recordPayload('default', [
                'source' => 'shadow',
                'event' => 'explanation_depth_preference',
                'depth' => 'short',
            ]);
        }

        $profile = $this->repository->findByScope('default');
        $mapper = new LearningProfileJsonMapper();
        $restored = $mapper->fromJson($mapper->toJson($profile));

        self::assertSame($profile->id()->value, $restored->id()->value);
        self::assertSame($profile->signals()->count(), $restored->signals()->count());
        self::assertSame($profile->insights()->count(), $restored->insights()->count());
        self::assertSame($profile->recommendations()->count(), $restored->recommendations()->count());
    }
}
