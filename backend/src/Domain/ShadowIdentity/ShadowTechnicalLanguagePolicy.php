<?php

declare(strict_types=1);

namespace App\Domain\ShadowIdentity;

enum ShadowTechnicalLanguagePolicy: string
{
    case AlwaysOriginal = 'always_original';
    case AlwaysTranslate = 'always_translate';
    case OriginalWithExplanation = 'original_with_explanation';
    case Adaptive = 'adaptive';
}
