<?php

declare(strict_types=1);

namespace App\Domain\ShadowRelationship;

enum SharedReferenceKind: string
{
    case Topic = 'topic';
    case Analogy = 'analogy';
    case Difficulty = 'difficulty';
    case Vocabulary = 'vocabulary';
}
