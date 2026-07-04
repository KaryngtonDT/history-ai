<?php

declare(strict_types=1);

namespace App\Domain\ShadowBrowser;

enum BrowserActionType: string
{
    case Explain = 'explain';
    case Translate = 'translate';
    case Summarize = 'summarize';
    case SaveToBrain = 'save_to_brain';
    case OpenWatch = 'open_watch';

    public static function tryFromAction(mixed $value): ?self
    {
        if (!is_string($value)) {
            return null;
        }

        return self::tryFrom($value);
    }
}
