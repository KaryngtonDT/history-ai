<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\ShadowRelationship;

use App\Application\ShadowRelationship\InterestDetector;
use App\Domain\ShadowRelationship\RelationshipTraitType;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class InterestDetectorTest extends TestCase
{
    #[Test]
    public function detectsPhilosophyInterestFromQuestionText(): void
    {
        $traits = (new InterestDetector())->detect([
            'question' => 'Can you explain Nietzsche in this segment?',
        ]);

        self::assertCount(1, $traits);
        self::assertSame(RelationshipTraitType::Interest, $traits[0]->type());
        self::assertSame('philosophy', $traits[0]->key());
    }
}
