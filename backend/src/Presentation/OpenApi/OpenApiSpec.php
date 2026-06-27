<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi;

use OpenApi\Attributes as OA;

#[OA\OpenApi(openapi: '3.1.0')]
#[OA\Info(
    title: 'History AI API',
    version: '1.0.0',
    description: 'Public REST API for content, artifacts, library, and collections. Supported artifact types: transcript, summary, quiz, flashcards, timeline, and podcast.',
)]
#[OA\Server(url: 'http://localhost:8000', description: 'Local development')]
#[OA\Tag(name: 'Contents', description: 'Content resources imported for processing')]
#[OA\Tag(name: 'Artifacts', description: 'Generated learning artifacts for a content resource')]
#[OA\Tag(name: 'Library', description: 'Saved library items curated by the user')]
#[OA\Tag(name: 'Collections', description: 'Themed groups of library items')]
#[OA\Tag(name: 'Search', description: 'Library search')]
#[OA\Tag(name: 'Timeline', description: 'Structured timeline projections for timeline artifacts')]
final class OpenApiSpec
{
}
