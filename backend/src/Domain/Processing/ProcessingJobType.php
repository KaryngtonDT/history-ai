<?php

declare(strict_types=1);

namespace App\Domain\Processing;

enum ProcessingJobType: string
{
    case Summary = 'summary';
    case Quiz = 'quiz';
    case Flashcards = 'flashcards';
    case Translation = 'translation';
    case Podcast = 'podcast';
    case Timeline = 'timeline';
    case MindMap = 'mind_map';
}
