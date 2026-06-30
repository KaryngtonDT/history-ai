<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Pipeline;

use App\Domain\Pipeline\Exception\InvalidPipelineConfigurationException;
use App\Domain\Pipeline\PipelineStage;
use App\Domain\Pipeline\PipelineStageCollection;
use App\Domain\Pipeline\PipelineStageType;
use PHPUnit\Framework\TestCase;

final class PipelineStageCollectionTest extends TestCase
{
    public function testFindByTypeReturnsMatchingStage(): void
    {
        $collection = PipelineStageCollection::fromStages([
            PipelineStage::create(PipelineStageType::SpeechToText, 'faster_whisper'),
            PipelineStage::create(PipelineStageType::Translation, 'ollama'),
        ]);

        $stage = $collection->findByType(PipelineStageType::Translation);

        self::assertNotNull($stage);
        self::assertSame('ollama', $stage->providerId());
    }

    public function testReplaceUpdatesExistingStage(): void
    {
        $collection = PipelineStageCollection::fromStages([
            PipelineStage::create(PipelineStageType::TextToSpeech, 'f5_tts'),
        ]);

        $updated = $collection->replace(
            PipelineStage::create(PipelineStageType::TextToSpeech, 'kokoro'),
        );

        self::assertSame('kokoro', $updated->findByType(PipelineStageType::TextToSpeech)?->providerId());
    }

    public function testDuplicateStageThrows(): void
    {
        $this->expectException(InvalidPipelineConfigurationException::class);

        new PipelineStageCollection([
            PipelineStage::create(PipelineStageType::SpeechToText, 'faster_whisper'),
            PipelineStage::create(PipelineStageType::SpeechToText, 'other'),
        ]);
    }
}
