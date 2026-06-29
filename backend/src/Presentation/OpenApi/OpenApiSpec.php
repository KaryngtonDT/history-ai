<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi;

use App\Presentation\OpenApi\Schema\ArtifactRelation;
use App\Presentation\OpenApi\Schema\ArtifactRelations;
use App\Presentation\OpenApi\Schema\ArtifactRelationTypeSchema;
use App\Presentation\OpenApi\Schema\ArtifactRecommendations;
use App\Presentation\OpenApi\Schema\Coordinates;
use App\Presentation\OpenApi\Schema\GraphEdge;
use App\Presentation\OpenApi\Schema\GraphNode;
use App\Presentation\OpenApi\Schema\HistoricalPlace;
use App\Presentation\OpenApi\Schema\KnowledgeGraph;
use App\Presentation\OpenApi\Schema\Map;
use App\Presentation\OpenApi\Schema\RecommendationReasonSchema;
use App\Presentation\OpenApi\Schema\RecommendedArtifact;
use App\Presentation\OpenApi\Schema\RetrievedChunk;
use App\Presentation\OpenApi\Schema\SemanticSearchResult;
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
#[OA\Tag(name: 'Map', description: 'Historical place map projections for timeline artifacts')]
#[OA\Tag(name: 'Relations', description: 'Deterministic artifact relation projections for content resources')]
#[OA\Tag(name: 'Graph', description: 'Knowledge graph projections for content resources')]
#[OA\Tag(name: 'Recommendations', description: 'Contextual artifact recommendation projections for content resources')]
#[OA\Tag(name: 'Semantic', description: 'Semantic chunk retrieval projections for content resources')]
#[OA\Tag(name: 'Chat', description: 'RAG chat answers for content resources')]
#[OA\Tag(name: 'Platform', description: 'Internal platform diagnostics and observability')]
final class OpenApiSpec
{
}
