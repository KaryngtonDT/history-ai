<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\TTS;

use App\Domain\TTS\Voice;
use App\Domain\TTS\VoiceCollection;
use App\Domain\TTS\VoiceGender;
use App\Domain\TTS\VoiceLanguage;
use PHPUnit\Framework\TestCase;

final class VoiceCollectionTest extends TestCase
{
    public function testEmptyCollection(): void
    {
        $collection = VoiceCollection::empty();

        self::assertTrue($collection->isEmpty());
        self::assertSame(0, $collection->count());
    }

    public function testFindByIdReturnsVoice(): void
    {
        $female = Voice::create('female_01', 'Female 01', VoiceLanguage::French, VoiceGender::Female);
        $male = Voice::create('male_01', 'Male 01', VoiceLanguage::French, VoiceGender::Male);
        $collection = new VoiceCollection([$female, $male]);

        self::assertSame($female, $collection->findById('female_01'));
        self::assertNull($collection->findById('unknown'));
        self::assertSame(2, $collection->count());
    }

    public function testAppendReturnsNewCollection(): void
    {
        $voice = Voice::create('female_01', 'Female 01', VoiceLanguage::German, VoiceGender::Female);
        $collection = VoiceCollection::empty()->append($voice);

        self::assertSame(1, $collection->count());
        self::assertSame($voice, $collection->all()[0]);
    }
}
