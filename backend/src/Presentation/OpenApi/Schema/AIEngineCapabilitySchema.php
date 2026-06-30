<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'AIEngineCapability',
    type: 'string',
    description: 'AI capability supported by the platform engine registry.',
    enum: ['speech_to_text', 'translation', 'text_to_speech', 'voice_clone', 'lip_sync'],
    example: 'translation',
)]
final class AIEngineCapabilitySchema
{
}
