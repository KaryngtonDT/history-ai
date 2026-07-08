<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Pipeline\Handlers;

use App\Application\Pipeline\Commands\SavePipelineConfigurationCommand;
use App\Application\Pipeline\Handlers\SavePipelineConfigurationHandler;
use App\Application\Pipeline\PipelineConfigurationValidator;
use App\Domain\AI\AIProviderResolverInterface;
use App\Domain\Pipeline\PipelineConfigurationRepositoryInterface;
use App\Application\Runtime\RuntimeSelectionSynchronizerInterface;
use App\Infrastructure\AI\AIEngineRegistryFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class SavePipelineConfigurationHandlerTest extends TestCase
{
    private PipelineConfigurationRepositoryInterface&MockObject $repository;

    private AIProviderResolverInterface $aiProviderResolver;

    private SavePipelineConfigurationHandler $handler;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(PipelineConfigurationRepositoryInterface::class);
        $registry = (new AIEngineRegistryFactory())->create();

        $this->aiProviderResolver = $this->createStub(AIProviderResolverInterface::class);
        $this->aiProviderResolver->method('registry')->willReturn($registry);

        $this->handler = new SavePipelineConfigurationHandler(
            $this->repository,
            new PipelineConfigurationValidator($this->aiProviderResolver),
            $this->createStub(RuntimeSelectionSynchronizerInterface::class),
        );
    }

    public function testSavesValidConfiguration(): void
    {
        $this->repository->method('findLatest')->willReturn(null);
        $this->repository->expects(self::once())->method('save');

        $result = ($this->handler)(new SavePipelineConfigurationCommand([
            ['stage' => 'speech_to_text', 'providerId' => 'faster_whisper'],
            ['stage' => 'translation', 'providerId' => 'ollama'],
            ['stage' => 'text_to_speech', 'providerId' => 'f5_tts'],
            ['stage' => 'voice_clone', 'providerId' => 'openvoice'],
            ['stage' => 'lip_sync', 'providerId' => 'latentsync'],
            ['stage' => 'video_render', 'providerId' => 'ffmpeg'],
        ]));

        self::assertSame(1, $result->version);
        self::assertCount(6, $result->stages);
    }
}
