<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Shadow;

use App\Domain\Shadow\Exception\InvalidShadowSessionException;
use App\Domain\Shadow\ShadowChallenge;
use App\Domain\Shadow\ShadowChallengeAnswer;
use App\Domain\Shadow\ShadowIntervention;
use App\Domain\Shadow\ShadowInterventionCollection;
use App\Domain\Shadow\ShadowInterventionId;
use App\Domain\Shadow\ShadowInterventionPolicy;
use App\Domain\Shadow\ShadowInterventionTrigger;
use App\Domain\Shadow\ShadowInterventionType;
use App\Domain\Shadow\ShadowTimestamp;
use App\Domain\Shadow\ShadowTutorMode;
use PHPUnit\Framework\TestCase;

final class ShadowInterventionTest extends TestCase
{
    public function testCreatesValidInterventionWithReason(): void
    {
        $intervention = ShadowIntervention::create(
            ShadowInterventionId::generate(),
            ShadowInterventionType::Explanation,
            ShadowInterventionTrigger::LowConfidenceTranslation,
            'Translation confidence is low for this segment.',
            ShadowTimestamp::fromSeconds(12.0),
            'Listen and confirm understanding',
            allowAutoPause: true,
            explanation: 'The speaker used an idiomatic expression.',
        );

        self::assertSame(
            ShadowInterventionType::Explanation,
            $intervention->type(),
        );
        self::assertStringContainsString('confidence', $intervention->reason());
    }

    public function testChallengeInterventionRequiresQuestion(): void
    {
        $this->expectException(InvalidShadowSessionException::class);

        ShadowIntervention::create(
            ShadowInterventionId::generate(),
            ShadowInterventionType::VocabularyCheck,
            ShadowInterventionTrigger::UnknownVocabulary,
            'New vocabulary detected.',
            ShadowTimestamp::fromSeconds(5.0),
            'Answer the vocabulary question',
            allowAutoPause: true,
        );
    }

    public function testChallengeInterventionAcceptsQuestion(): void
    {
        $intervention = ShadowIntervention::create(
            ShadowInterventionId::generate(),
            ShadowInterventionType::VocabularyCheck,
            ShadowInterventionTrigger::UnknownVocabulary,
            'New vocabulary detected.',
            ShadowTimestamp::fromSeconds(5.0),
            'Answer the vocabulary question',
            allowAutoPause: true,
            challenge: ShadowChallenge::create('What does compound interest mean?'),
        );

        self::assertNotNull($intervention->challenge());
    }

    public function testRejectsEmptyReason(): void
    {
        $this->expectException(InvalidShadowSessionException::class);

        ShadowIntervention::create(
            ShadowInterventionId::generate(),
            ShadowInterventionType::SummaryPrompt,
            ShadowInterventionTrigger::TopicShift,
            '   ',
            ShadowTimestamp::fromSeconds(1.0),
            'Summarize',
            allowAutoPause: false,
        );
    }
}
