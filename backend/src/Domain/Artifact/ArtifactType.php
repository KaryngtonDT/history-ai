<?php

declare(strict_types=1);

namespace App\Domain\Artifact;

enum ArtifactType: string
{
    case Summary = 'summary';
    case Quiz = 'quiz';
    case Flashcards = 'flashcards';
    case Podcast = 'podcast';
    case Timeline = 'timeline';
    case Transcript = 'transcript';
    case Translation = 'translation';
}
