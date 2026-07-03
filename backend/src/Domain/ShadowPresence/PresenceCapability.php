<?php

declare(strict_types=1);

namespace App\Domain\ShadowPresence;

enum PresenceCapability: string
{
    case AskQuestion = 'ask_question';
    case SearchBrain = 'search_brain';
    case ResumeConversation = 'resume_conversation';
    case ReadSelection = 'read_selection';
    case ReadPageContext = 'read_page_context';
    case ReadWorkspace = 'read_workspace';
    case ProactiveHint = 'proactive_hint';
}
