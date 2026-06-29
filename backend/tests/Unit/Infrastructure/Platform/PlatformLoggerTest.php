<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Platform;

use App\Domain\Platform\CorrelationId;
use App\Infrastructure\Platform\PlatformLogger;
use App\Tests\Unit\Application\Platform\Support\FixedRequestContextProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\AbstractLogger;

final class PlatformLoggerTest extends TestCase
{
    public function testInjectsCorrelationIdTimestampAndComponent(): void
    {
        $correlationId = new CorrelationId('c6f98b8a-3f2e-4a1b-9c8d-1e2f3a4b5c6d');
        $recordingLogger = new RecordingPsrLogger();
        $logger = new PlatformLogger(
            $recordingLogger,
            new FixedRequestContextProvider($correlationId),
        );

        $logger->info('SearchSemanticChunksHandler', 'request started', ['contentId' => 'content-1']);

        self::assertCount(1, $recordingLogger->records);
        self::assertSame('request started', $recordingLogger->records[0]['message']);
        self::assertSame('c6f98b8a-3f2e-4a1b-9c8d-1e2f3a4b5c6d', $recordingLogger->records[0]['context']['correlationId']);
        self::assertSame('SearchSemanticChunksHandler', $recordingLogger->records[0]['context']['component']);
        self::assertSame('content-1', $recordingLogger->records[0]['context']['contentId']);
        self::assertIsString($recordingLogger->records[0]['context']['timestamp']);
    }
}

final class RecordingPsrLogger extends AbstractLogger
{
    /** @var list<array{message: string, context: array<string, mixed>}> */
    public array $records = [];

    public function log($level, string|\Stringable $message, array $context = []): void
    {
        $this->records[] = [
            'message' => (string) $message,
            'context' => $context,
        ];
    }
}
