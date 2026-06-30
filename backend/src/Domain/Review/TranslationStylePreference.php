<?php

declare(strict_types=1);

namespace App\Domain\Review;

enum TranslationStylePreference: string
{
    case Natural = 'natural';
    case Literal = 'literal';
    case Balanced = 'balanced';
}
