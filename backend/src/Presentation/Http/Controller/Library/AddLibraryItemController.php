<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Library;

use App\Application\Library\Commands\AddLibraryItemCommand;
use App\Application\Library\Handlers\AddLibraryItemHandler;
use App\Domain\Artifact\Exception\InvalidArtifactException;
use App\Domain\Content\Exception\InvalidContentIdException;
use App\Domain\Library\Exception\InvalidLibraryItemTitleException;
use App\Presentation\Http\Request\Library\AddLibraryItemRequest;
use App\Presentation\Http\Request\Library\Exception\InvalidLibraryRequestException;
use App\Presentation\Http\Response\Library\AddLibraryItemResponse;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AddLibraryItemController extends AbstractController
{
    #[OA\Post(
        operationId: 'addLibraryItem',
        summary: 'Add library item',
        description: 'Saves an artifact from a content resource into the user library.',
        tags: ['Library'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['contentId', 'artifactId', 'type', 'title'],
                properties: [
                    new OA\Property(property: 'contentId', type: 'string', format: 'uuid'),
                    new OA\Property(property: 'artifactId', type: 'string', format: 'uuid'),
                    new OA\Property(
                        property: 'type',
                        type: 'string',
                        enum: ['summary', 'quiz', 'flashcards', 'transcript'],
                        example: 'summary',
                    ),
                    new OA\Property(property: 'title', type: 'string', example: 'Roman Empire Summary'),
                ],
            ),
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Library item created',
                content: new OA\JsonContent(
                    required: ['id', 'type', 'title', 'createdAt'],
                    properties: [
                        new OA\Property(property: 'id', type: 'string', format: 'uuid'),
                        new OA\Property(property: 'type', type: 'string', example: 'summary'),
                        new OA\Property(property: 'title', type: 'string', example: 'Roman Empire Summary'),
                        new OA\Property(property: 'createdAt', type: 'string', format: 'date-time'),
                    ],
                ),
            ),
            new OA\Response(
                response: 400,
                description: 'Invalid request',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse'),
            ),
        ],
    )]
    #[Route('/api/library/items', name: 'api_library_items_create', methods: ['POST'])]
    public function __invoke(Request $request, AddLibraryItemHandler $handler): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);

        if (!is_array($payload)) {
            return $this->invalidRequestResponse();
        }

        try {
            $addRequest = AddLibraryItemRequest::fromArray($payload);
        } catch (InvalidLibraryRequestException) {
            return $this->invalidRequestResponse();
        }

        try {
            $result = $handler(new AddLibraryItemCommand(
                contentId: $addRequest->contentId,
                artifactId: $addRequest->artifactId,
                type: $addRequest->type,
                title: $addRequest->title,
            ));
        } catch (InvalidContentIdException|InvalidArtifactException|InvalidLibraryItemTitleException) {
            return $this->invalidRequestResponse();
        }

        return $this->json(
            AddLibraryItemResponse::fromResult($result)->toArray(),
            Response::HTTP_CREATED,
        );
    }

    private function invalidRequestResponse(): JsonResponse
    {
        return $this->json(['error' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
    }
}
