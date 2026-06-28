<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Relation;

use App\Application\Relation\Handlers\GetArtifactRelationsHandler;
use App\Application\Relation\Queries\GetArtifactRelationsQuery;
use App\Domain\Content\ContentId;
use App\Domain\Content\Exception\InvalidContentIdException;
use App\Presentation\Http\Response\Relation\ArtifactRelationsResponse;
use App\Presentation\OpenApi\Schema\ArtifactRelation;
use App\Presentation\OpenApi\Schema\ArtifactRelations;
use App\Presentation\OpenApi\Schema\ArtifactRelationTypeSchema;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GetArtifactRelationsController extends AbstractController
{
    #[OA\Get(
        operationId: 'getArtifactRelations',
        summary: 'Get artifact relations for content',
        description: 'Returns deterministic relations between artifacts generated for a content resource.',
        tags: ['Relations'],
        parameters: [
            new OA\Parameter(
                name: 'contentId',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'uuid'),
                example: '550e8400-e29b-41d4-a716-446655440000',
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Artifact relations projection',
                content: new OA\JsonContent(ref: '#/components/schemas/ArtifactRelations'),
            ),
            new OA\Response(
                response: 400,
                description: 'Invalid request',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse'),
            ),
        ],
    )]
    #[Route(
        '/api/contents/{contentId}/relations',
        name: 'api_contents_relations_get',
        methods: ['GET'],
    )]
    public function __invoke(
        string $contentId,
        GetArtifactRelationsHandler $handler,
    ): JsonResponse {
        try {
            new ContentId($contentId);
        } catch (InvalidContentIdException) {
            return $this->json(['error' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
        }

        $result = $handler(new GetArtifactRelationsQuery($contentId));

        return $this->json(ArtifactRelationsResponse::fromResult($result));
    }
}
