<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\ShadowRelationship;

use App\Application\ShadowRelationship\ConversationStyleDetector;
use App\Application\ShadowRelationship\HabitDetector;
use App\Application\ShadowRelationship\InterestDetector;
use App\Application\ShadowRelationship\MotivationDetector;
use App\Application\ShadowRelationship\RelationshipEvolutionEngine;
use App\Application\ShadowRelationship\RelationshipJsonMapper;
use App\Application\ShadowRelationship\RelationshipTraitResolver;
use App\Application\ShadowRelationship\RelationshipUtteranceInterpreter;
use App\Infrastructure\ShadowRelationship\InMemoryShadowRelationshipRepository;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class RelationshipUtteranceInterpreterTest extends TestCase
{
    #[Test]
    public function footballAnalogyRequestRequiresConfirmationUntilApplied(): void
    {
        $repository = new InMemoryShadowRelationshipRepository();
        $engine = new RelationshipEvolutionEngine(
            new InterestDetector(),
            new HabitDetector(),
            new MotivationDetector(),
            new ConversationStyleDetector(),
            new RelationshipTraitResolver(),
        );
        $interpreter = new RelationshipUtteranceInterpreter(
            $repository,
            $engine,
            new RelationshipJsonMapper(),
        );

        $preview = $interpreter->interpret('Shadow, remember that I like football analogies.');

        self::assertTrue($preview['requiresConfirmation']);
        self::assertSame('remember_habit', $preview['intent']);
        self::assertFalse($preview['applied']);

        $applied = $interpreter->interpret('Shadow, remember that I like football analogies.', 'default', true);

        self::assertTrue($applied['applied']);
        self::assertSame('Explain with football analogies', $applied['previewLabel']);
    }
}
