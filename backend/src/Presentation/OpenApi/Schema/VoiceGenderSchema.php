<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'VoiceGender',
    type: 'string',
    enum: ['male', 'female', 'neutral'],
    example: 'female',
)]
final class VoiceGenderSchema
{
}
