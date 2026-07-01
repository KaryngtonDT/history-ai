<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Shadow;

use App\Domain\Shadow\Exception\InvalidShadowSessionException;
use App\Domain\Shadow\ShadowChallengeAnswer;
use PHPUnit\Framework\TestCase;

final class ShadowChallengeAnswerTest extends TestCase
{
    public function testCreatesAnswerFromString(): void
    {
        $answer = ShadowChallengeAnswer::fromString('  Compound interest grows on prior interest.  ');

        self::assertSame(
            'Compound interest grows on prior interest.',
            $answer->text(),
        );
    }

    public function testRejectsEmptyAnswer(): void
    {
        $this->expectException(InvalidShadowSessionException::class);
        ShadowChallengeAnswer::fromString('');
    }
}
