<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Shadow;

use App\Domain\Shadow\Exception\InvalidShadowSessionException;
use App\Domain\Shadow\ShadowChallenge;
use PHPUnit\Framework\TestCase;

final class ShadowChallengeTest extends TestCase
{
    public function testCreatesChallengeWithQuestion(): void
    {
        $challenge = ShadowChallenge::create(
            '  What does this word mean?  ',
            'It refers to compound growth.',
        );

        self::assertSame('What does this word mean?', $challenge->questionText());
        self::assertSame('It refers to compound growth.', $challenge->suggestedAnswer());
    }

    public function testRejectsEmptyQuestion(): void
    {
        $this->expectException(InvalidShadowSessionException::class);
        ShadowChallenge::create('   ');
    }
}
