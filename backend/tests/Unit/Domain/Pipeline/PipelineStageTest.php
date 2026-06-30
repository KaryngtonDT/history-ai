<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Pipeline;

use App\Domain\Pipeline\Exception\InvalidPipelineConfigurationException;
use App\Domain\Pipeline\PipelineStage;
use App\Domain\Pipeline\PipelineStageType;
use PHPUnit\Framework\TestCase;

final class PipelineStageTest extends TestCase
{
    public function testCreateExposesFields(): void
    {
        $stage = PipelineStage::create(PipelineStageType::Translation, 'ollama');

        self::assertSame(PipelineStageType::Translation, $stage->stage());
        self::assertSame('ollama', $stage->providerId());
    }

    public function testEmptyProviderIdThrows(): void
    {
        $this->expectException(InvalidPipelineConfigurationException::class);

        new PipelineStage(PipelineStageType::Translation, '   ');
    }

    public function testWithProviderIdReturnsNewInstance(): void
    {
        $stage = PipelineStage::create(PipelineStageType::LipSync, 'latentsync');
        $updated = $stage->withProviderId('wav2lip');

        self::assertSame('latentsync', $stage->providerId());
        self::assertSame('wav2lip', $updated->providerId());
    }
}
