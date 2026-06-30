<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ReviewComment',
    type: 'string',
    maxLength: 2000,
    example: 'The cloned voice is slightly too robotic.',
)]
final class ReviewCommentSchema
{
}
