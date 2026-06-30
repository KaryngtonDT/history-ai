<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\VideoRender;

use App\Domain\VideoRender\VideoRenderQuality;
use PHPUnit\Framework\TestCase;

final class VideoRenderQualityTest extends TestCase
{
    public function testContainsExpectedQualities(): void
    {
        self::assertSame('preview', VideoRenderQuality::Preview->value);
        self::assertSame('standard', VideoRenderQuality::Standard->value);
        self::assertSame('high', VideoRenderQuality::High->value);
    }
}
