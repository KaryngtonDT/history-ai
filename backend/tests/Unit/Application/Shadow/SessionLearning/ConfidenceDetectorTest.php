<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Shadow\SessionLearning;

use App\Application\Shadow\SessionLearning\ConfidenceDetector;
use App\Domain\Shadow\SessionLearning\PedagogicalConfidence;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ConfidenceDetectorTest extends TestCase
{
    private ConfidenceDetector $detector;

    protected function setUp(): void
    {
        $this->detector = new ConfidenceDetector();
    }

    #[Test]
    public function repeatedQuestionsMarkStruggling(): void
    {
        self::assertSame(
            PedagogicalConfidence::Struggling,
            $this->detector->detect(2, 0, 0, 0),
        );
    }

    #[Test]
    public function challengeSuccessMarksGrowing(): void
    {
        self::assertSame(
            PedagogicalConfidence::Growing,
            $this->detector->detect(0, 0, 2, 0),
        );
    }
}
