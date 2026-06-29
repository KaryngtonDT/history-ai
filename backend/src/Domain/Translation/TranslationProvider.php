<?php

declare(strict_types=1);

namespace App\Domain\Translation;

enum TranslationProvider: string
{
    case Qwen = 'qwen';
    case DeepSeek = 'deepseek';
    case Gemini = 'gemini';
    case Gpt = 'gpt';
    case Mock = 'mock';
}
