<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Learning;

use App\Domain\Learning\Exception\InvalidLearningProfileException;
use App\Domain\Learning\LearningSignal;
use App\Domain\Learning\LearningSignalType;
use PHPUnit\Framework\TestCase;

final class LearningSignalTest extends TestCase
{
    public function testRecordsSignalWithSummary(): void
    {
        $signal = LearningSignal::record(
            LearningSignalType::ShadowChallengeAnswered,
            ['summary' => 'Answered vocabulary challenge correctly'],
        );

        self::assertSame(LearningSignalType::ShadowChallengeAnswered, $signal->type());
        self::assertSame('Answered vocabulary challenge correctly', $signal->context()['summary']);
    }

    public function testRejectsEmptyContext(): void
    {
        $this->expectException(InvalidLearningProfileException::class);

        new LearningSignal(
            \App\Domain\Learning\LearningSignalId::generate(),
            LearningSignalType::GrammarDifficulty,
            new \DateTimeImmutable(),
            [],
        );
    }
}
