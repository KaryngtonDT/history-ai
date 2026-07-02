<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Shadow\SessionLearning;

use App\Application\Shadow\SessionLearning\TeachingStrategyResolver;
use App\Domain\Shadow\SessionLearning\PedagogicalConfidence;
use App\Domain\Shadow\SessionLearning\PedagogicalFatigue;
use App\Domain\Shadow\SessionLearning\SessionLearningState;
use App\Domain\Shadow\SessionLearning\TeachingStrategyKind;
use App\Domain\Shadow\ShadowChallengeLevel;
use App\Domain\Shadow\ShadowExplanationStyle;
use App\Domain\Shadow\ShadowSessionId;
use App\Domain\Video\VideoId;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class TeachingStrategyResolverTest extends TestCase
{
    #[Test]
    public function recoveryStrategyUsesExampleFirstExplanations(): void
    {
        $state = SessionLearningState::start(
            new ShadowSessionId('11111111-1111-4111-8111-111111111111'),
            new VideoId('22222222-2222-4222-8222-222222222222'),
        )->withDerivedState(
            \App\Domain\Shadow\SessionLearning\PedagogicalAttention::Low,
            PedagogicalFatigue::High,
            PedagogicalConfidence::Struggling,
            \App\Domain\Shadow\SessionLearning\PedagogicalPace::Slow,
            \App\Domain\Shadow\SessionLearning\PedagogicalEnergy::Low,
            \App\Domain\Shadow\SessionLearning\PedagogicalDifficulty::Easy,
            TeachingStrategyKind::Recovery,
            \App\Domain\Shadow\SessionLearning\SpeakingPaceKind::Slow,
            \App\Domain\Shadow\SessionLearning\SessionVoiceStyleKind::Calm,
            \App\Domain\Shadow\SessionLearning\StrategyAdjustmentCollection::empty(),
            5,
            2,
            3,
            1,
            0,
            0,
            2,
            0,
        );

        $strategy = (new TeachingStrategyResolver())->resolve($state);

        self::assertSame(TeachingStrategyKind::Recovery, $strategy->kind());
        self::assertSame(ShadowExplanationStyle::ExampleFirst, $strategy->explanationStyle());
        self::assertSame(ShadowChallengeLevel::Easy, $strategy->challengeLevel());
        self::assertTrue($strategy->offerPausePrompt());
    }
}
