<?php

declare(strict_types=1);

namespace App\Domain\ShadowIdentity;

enum ShadowConversationStyle: string
{
    case Academic = 'academic';
    case Friendly = 'friendly';
    case Conversational = 'conversational';
    case Socratic = 'socratic';
    case Debate = 'debate';
    case Coach = 'coach';
}
