<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Relation;

use App\Domain\Relation\ArtifactRelationType;
use PHPUnit\Framework\TestCase;

final class ArtifactRelationTypeTest extends TestCase
{
    public function testDefinesAllExpectedValues(): void
    {
        self::assertSame(
            [
                'related',
                'derived_from',
                'references',
                'next',
                'previous',
            ],
            array_map(
                static fn (ArtifactRelationType $type): string => $type->value,
                ArtifactRelationType::cases(),
            ),
        );
    }

    public function testCanBeCreatedFromStringValue(): void
    {
        self::assertSame(ArtifactRelationType::Related, ArtifactRelationType::from('related'));
        self::assertSame(ArtifactRelationType::DerivedFrom, ArtifactRelationType::from('derived_from'));
        self::assertSame(ArtifactRelationType::References, ArtifactRelationType::from('references'));
        self::assertSame(ArtifactRelationType::Next, ArtifactRelationType::from('next'));
        self::assertSame(ArtifactRelationType::Previous, ArtifactRelationType::from('previous'));
    }
}
