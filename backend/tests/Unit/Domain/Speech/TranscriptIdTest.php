<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Speech;

use App\Domain\Speech\Exception\InvalidTranscriptException;
use App\Domain\Speech\TranscriptId;
use PHPUnit\Framework\TestCase;

final class TranscriptIdTest extends TestCase
{
    private const string TRANSCRIPT_ID = '550e8400-e29b-41d4-a716-446655440010';

    public function testAcceptsValidUuid(): void
    {
        $id = new TranscriptId(self::TRANSCRIPT_ID);

        self::assertSame(self::TRANSCRIPT_ID, $id->value);
    }

    public function testGenerateCreatesValidUuid(): void
    {
        $id = TranscriptId::generate();

        self::assertTrue(TranscriptId::isValid($id->value));
    }

    public function testEqualsComparesValue(): void
    {
        $first = new TranscriptId(self::TRANSCRIPT_ID);
        $second = new TranscriptId(self::TRANSCRIPT_ID);
        $third = TranscriptId::generate();

        self::assertTrue($first->equals($second));
        self::assertFalse($first->equals($third));
    }

    public function testRejectsInvalidUuid(): void
    {
        $this->expectException(InvalidTranscriptException::class);

        new TranscriptId('not-a-uuid');
    }
}
