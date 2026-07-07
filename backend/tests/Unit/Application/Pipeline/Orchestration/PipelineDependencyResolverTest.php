<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Pipeline\Orchestration;

use App\Application\Pipeline\Orchestration\PipelineDependencyResolver;
use App\Domain\Pipeline\PipelineStageType;
use PHPUnit\Framework\TestCase;

final class PipelineDependencyResolverTest extends TestCase
{
    private PipelineDependencyResolver $resolver;

    protected function setUp(): void
    {
        $this->resolver = new PipelineDependencyResolver();
    }

    public function testSpeechToTextInvalidatesDownstreamStages(): void
    {
        $invalidated = $this->resolver->invalidatesStages(PipelineStageType::SpeechToText);

        self::assertContains('translation', $invalidated);
        self::assertContains('text_to_speech', $invalidated);
        self::assertContains('quality', $invalidated);
    }

    public function testNextStageAfterSpeechToTextIsTranslation(): void
    {
        self::assertSame(
            PipelineStageType::Translation,
            $this->resolver->nextStage(PipelineStageType::SpeechToText),
        );
    }
}
