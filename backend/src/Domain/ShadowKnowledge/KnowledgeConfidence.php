<?php

declare(strict_types=1);

namespace App\Domain\ShadowKnowledge;

enum KnowledgeConfidence: string
{
    case Low = 'low';
    case Medium = 'medium';
    case High = 'high';
}
