<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Video;

use App\Domain\Video\VideoStatus;
use PHPUnit\Framework\TestCase;

final class VideoStatusTest extends TestCase
{
    public function testExposesLifecycleStatuses(): void
    {
        self::assertSame('uploaded', VideoStatus::Uploaded->value);
        self::assertSame('queued', VideoStatus::Queued->value);
        self::assertSame('processing', VideoStatus::Processing->value);
        self::assertSame('completed', VideoStatus::Completed->value);
        self::assertSame('failed', VideoStatus::Failed->value);
    }
}
