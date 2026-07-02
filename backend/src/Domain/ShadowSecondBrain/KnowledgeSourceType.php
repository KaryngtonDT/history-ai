<?php

declare(strict_types=1);

namespace App\Domain\ShadowSecondBrain;

enum KnowledgeSourceType: string
{
    case Video = 'video';
    case Pdf = 'pdf';
    case Audio = 'audio';
    case Youtube = 'youtube';
    case Conversation = 'conversation';
    case Mission = 'mission';
    case Exercise = 'exercise';
    case Teaching = 'teaching';
}
