<?php

declare(strict_types=1);

namespace App\Tests\Functional\Console\Semantic;

use App\Infrastructure\Semantic\GeminiEmbeddingProvider;
use App\Infrastructure\Semantic\GeminiEmbeddingTransportInterface;
use App\Presentation\Console\Command\Semantic\GeminiEmbeddingSmokeTestCommand;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

final class GeminiEmbeddingSmokeTestCommandTest extends TestCase
{
    private GeminiEmbeddingTransportInterface&MockObject $transport;

    protected function setUp(): void
    {
        $this->transport = $this->createMock(GeminiEmbeddingTransportInterface::class);
    }

    public function testCommandFailsClearlyWhenApiKeyMissing(): void
    {
        $this->transport
            ->expects(self::never())
            ->method('post');

        $tester = new CommandTester($this->createCommand(apiKey: ''));

        $exitCode = $tester->execute(['text' => 'Roman Empire']);

        self::assertSame(Command::FAILURE, $exitCode);
        self::assertStringContainsString('GEMINI_API_KEY is not configured.', $tester->getDisplay());
    }

    public function testCommandPrintsDimensionAndSampleValuesWhenProviderReturnsVector(): void
    {
        $this->transport
            ->expects(self::once())
            ->method('post')
            ->willReturn(json_encode([
                'embedding' => ['values' => [0.1, -0.2, 0.3, -0.4, 0.5, 0.6]],
            ], JSON_THROW_ON_ERROR));

        $tester = new CommandTester($this->createCommand(
            apiKey: 'test-api-key',
            model: 'text-embedding-004',
        ));

        $exitCode = $tester->execute(['text' => 'Roman Empire']);

        self::assertSame(Command::SUCCESS, $exitCode);
        $display = $tester->getDisplay();
        self::assertStringContainsString('provider: gemini', $display);
        self::assertStringContainsString('model: text-embedding-004', $display);
        self::assertStringContainsString('dimension: 6', $display);
        self::assertStringContainsString('sample values: [0.1000, -0.2000, 0.3000, -0.4000, 0.5000]', $display);
    }

    public function testCommandDoesNotRequireRealApiKey(): void
    {
        $this->transport
            ->expects(self::once())
            ->method('post')
            ->willReturn(json_encode([
                'embedding' => ['values' => [0.42]],
            ], JSON_THROW_ON_ERROR));

        $tester = new CommandTester($this->createCommand(apiKey: 'fake-key-for-test'));

        self::assertSame(Command::SUCCESS, $tester->execute(['text' => 'Roman Empire']));
    }

    private function createCommand(
        string $apiKey = 'test-api-key',
        string $model = GeminiEmbeddingProvider::DEFAULT_MODEL,
    ): GeminiEmbeddingSmokeTestCommand {
        return new GeminiEmbeddingSmokeTestCommand(
            $this->transport,
            $apiKey,
            $model,
        );
    }
}
