<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Artifact;

use App\Application\Artifact\Handlers\ListArtifactsByContentHandler;
use App\Application\Artifact\Queries\ListArtifactsByContentQuery;
use App\Domain\Content\ContentId;
use App\Domain\Content\Exception\InvalidContentIdException;
use App\Presentation\Http\Response\Artifact\ListArtifactsResponse;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ListArtifactsByContentController extends AbstractController
{
    #[OA\Get(
        operationId: 'listArtifactsByContent',
        summary: 'List artifacts for content',
        description: 'Returns generated artifacts (summary, quiz, flashcards, etc.) for a content resource.',
        tags: ['Artifacts'],
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
                description: 'Artifact list',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        required: ['id', 'contentId', 'processingJobId', 'type', 'content', 'createdAt'],
                        properties: [
                            new OA\Property(property: 'id', type: 'string', format: 'uuid'),
                            new OA\Property(property: 'contentId', type: 'string', format: 'uuid'),
                            new OA\Property(property: 'processingJobId', type: 'string', format: 'uuid'),
                            new OA\Property(property: 'type', type: 'string', example: 'summary'),
                            new OA\Property(property: 'content', type: 'string'),
                            new OA\Property(property: 'createdAt', type: 'string', format: 'date-time'),
                        ],
                    ),
                ),
            ),
            new OA\Response(
                response: 400,
                description: 'Invalid content id',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse'),
            ),
        ],
    )]
    #[Route(
        '/api/contents/{contentId}/artifacts',
        name: 'api_contents_artifacts_list',
        methods: ['GET'],
    )]
    public function __invoke(
        string $contentId,
        ListArtifactsByContentHandler $handler,
    ): JsonResponse {
        try {
            new ContentId($contentId);
        } catch (InvalidContentIdException) {
            return $this->json(['error' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
        }

        $result = $handler(new ListArtifactsByContentQuery($contentId));

        return $this->json(ListArtifactsResponse::fromResult($result));
    }
}
