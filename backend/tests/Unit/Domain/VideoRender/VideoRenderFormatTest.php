<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\VideoRender;

use App\Domain\VideoRender\VideoRenderFormat;
use PHPUnit\Framework\TestCase;

final class VideoRenderFormatTest extends TestCase
{
    public function testContainsExpectedFormats(): void
    {
        self::assertSame('mp4', VideoRenderFormat::MP4->value);
        self::assertSame('webm', VideoRenderFormat::WEBM->value);
    }
}
