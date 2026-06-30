<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'LibraryItemType',
    type: 'string',
    description: 'Artifact type saved as a library item.',
    enum: ['summary', 'quiz', 'flashcards', 'transcript', 'translation', 'audio', 'timeline', 'podcast'],
    example: 'timeline',
)]
final class LibraryItemTypeSchema
{
}
