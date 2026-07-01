<?php

declare(strict_types=1);

namespace App\Domain\Shadow;

enum ShadowInteractionKind: string
{
    case Question = 'question';
    case Answer = 'answer';
    case Pause = 'pause';
    case Resume = 'resume';
}
