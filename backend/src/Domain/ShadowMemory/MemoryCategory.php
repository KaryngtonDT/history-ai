<?php

declare(strict_types=1);

namespace App\Domain\ShadowMemory;

enum MemoryCategory: string
{
    case Concept = 'concept';
    case Vocabulary = 'vocabulary';
    case Correction = 'correction';
    case Question = 'question';
    case Explanation = 'explanation';
    case Challenge = 'challenge';
    case Discovery = 'discovery';
    case Milestone = 'milestone';
    case Preference = 'preference';
    case Relationship = 'relationship';
}
