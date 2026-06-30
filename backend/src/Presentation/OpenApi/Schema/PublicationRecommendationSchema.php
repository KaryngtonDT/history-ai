<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'PublicationRecommendation',
    type: 'string',
    enum: ['ready', 'review_recommended', 'regenerate_required'],
    example: 'ready',
)]
final class PublicationRecommendationSchema
{
}
