<?php

declare(strict_types=1);

namespace App\Domain\ShadowBrowser;

enum BrowserPermission: string
{
    case AskQuestion = 'ask_question';
    case SearchBrain = 'search_brain';
    case ResumeConversation = 'resume_conversation';
    case ReadSelection = 'read_selection';
    case ReadPageContext = 'read_page_context';
    case DetectPlatform = 'detect_platform';
    case CaptureUrl = 'capture_url';
    case ProactiveHint = 'proactive_hint';
}
