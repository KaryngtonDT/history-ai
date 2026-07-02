<?php

declare(strict_types=1);

namespace App\Domain\ShadowKnowledge;

enum KnowledgeNodeType: string
{
    case Concept = 'concept';
    case Technology = 'technology';
    case Language = 'language';
    case Framework = 'framework';
    case Video = 'video';
    case Exercise = 'exercise';
    case Mission = 'mission';
    case Vocabulary = 'vocabulary';
    case Question = 'question';
}
