<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\VideoIntelligence;

use App\Domain\VideoIntelligence\LipVisibility;
use App\Domain\VideoIntelligence\VideoAnalyzerInput;
use App\Infrastructure\VideoIntelligence\VisualAnalyzer;
use PHPUnit\Framework\TestCase;

final class VisualAnalyzerTest extends TestCase
{
    public function testHighResolutionProducesExcellentLipVisibility(): void
    {
        $analyzer = new VisualAnalyzer();
        $input = VideoAnalyzerInput::create(
            'english',
            300.0,
            '1920x1080',
            30.0,
            20,
            'sample transcript',
            true,
            8.0,
        );

        $visual = $analyzer->analyze($input, 2);

        self::assertSame(LipVisibility::Excellent, $visual->lipVisibility());
        self::assertSame(2, $visual->faceCount());
    }
}
