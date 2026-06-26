<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Processing;

use App\Application\Processing\Ports\ProcessingOrchestratorInterface;
use App\Domain\Content\ContentId;
use App\Domain\Processing\ProcessingJob;
use App\Domain\Processing\ProcessingJobId;
use App\Domain\Processing\ProcessingJobType;
use App\Infrastructure\Processing\HttpWorkerDispatcher;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class HttpWorkerDispatcherTest extends TestCase
{
    public function testImplementsProcessingOrchestratorInterface(): void
    {
        $dispatcher = new HttpWorkerDispatcher('http://worker:8001');

        self::assertInstanceOf(ProcessingOrchestratorInterface::class, $dispatcher);
    }

    public function testDispatchThrowsWhenWorkerIsUnreachable(): void
    {
        $job = ProcessingJob::create(
            new ProcessingJobId('550e8400-e29b-41d4-a716-446655440000'),
            new ContentId('660e8400-e29b-41d4-a716-446655440001'),
            ProcessingJobType::Summary,
        );

        $dispatcher = new HttpWorkerDispatcher('http://127.0.0.1:1');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Worker dispatch failed');

        $dispatcher->dispatch($job);
    }
}
