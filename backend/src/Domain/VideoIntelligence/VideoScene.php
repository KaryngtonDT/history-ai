<?php

declare(strict_types=1);

namespace App\Domain\VideoIntelligence;

enum VideoScene: string
{
    case Interview = 'interview';
    case Presentation = 'presentation';
    case Podcast = 'podcast';
    case Conversation = 'conversation';
    case Lecture = 'lecture';
    case Other = 'other';
}
