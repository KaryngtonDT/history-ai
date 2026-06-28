<?php

declare(strict_types=1);

namespace App\Domain\Recommendation;

enum RecommendationReason: string
{
    case Related = 'related';
    case References = 'references';
    case DerivedFrom = 'derived_from';
    case Next = 'next';
    case Previous = 'previous';
}
