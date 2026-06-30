<?php

declare(strict_types=1);

namespace App\Domain\Library;

enum LibraryItemType: string
{
    case Summary = 'summary';
    case Quiz = 'quiz';
    case Flashcards = 'flashcards';
    case Transcript = 'transcript';
    case Translation = 'translation';
    case Audio = 'audio';
    case VoiceClone = 'voice_clone';
    case Timeline = 'timeline';
    case Podcast = 'podcast';
}
