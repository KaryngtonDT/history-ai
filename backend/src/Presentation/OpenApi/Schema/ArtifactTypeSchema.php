<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ArtifactType',
    type: 'string',
    description: 'Kind of generated learning artifact produced by the processing pipeline.',
    enum: ['transcript', 'translation', 'audio', 'voice_clone', 'summary', 'quiz', 'flashcards', 'timeline', 'podcast'],
    example: 'timeline',
)]
final class ArtifactTypeSchema
{
}
