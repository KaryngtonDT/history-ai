<?php

declare(strict_types=1);

namespace App\Domain\Quality;

enum PublicationRecommendation: string
{
    case Ready = 'ready';
    case ReviewRecommended = 'review_recommended';
    case RegenerateRequired = 'regenerate_required';
}
