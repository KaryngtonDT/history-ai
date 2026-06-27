<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Collection;

use App\Application\Collection\Commands\AssignLibraryItemToCollectionCommand;
use App\Application\Collection\Handlers\AssignLibraryItemToCollectionHandler;
use App\Domain\Collection\CollectionId;
use App\Domain\Collection\Exception\InvalidCollectionException;
use App\Domain\CollectionItem\Exception\CollectionItemAlreadyExistsException;
use App\Domain\Library\Exception\InvalidLibraryItemException;
use App\Presentation\Http\Request\Collection\AssignLibraryItemToCollectionRequest;
use App\Presentation\Http\Request\Collection\Exception\InvalidCollectionRequestException;
use App\Presentation\Http\Response\Collection\AssignLibraryItemToCollectionResponse;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AssignLibraryItemToCollectionController extends AbstractController
{
    #[OA\Post(
        operationId: 'assignLibraryItemToCollection',
        summary: 'Assign library item to collection',
        description: 'Links an existing library item to a collection. Returns 409 if the item is already assigned.',
        tags: ['Collections'],
        parameters: [
            new OA\Parameter(
                name: 'collectionId',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'uuid'),
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['libraryItemId'],
                properties: [
                    new OA\Property(property: 'libraryItemId', type: 'string', format: 'uuid'),
                ],
            ),
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Item assigned to collection',
                content: new OA\JsonContent(
                    required: ['id', 'collectionId', 'libraryItemId', 'createdAt'],
                    properties: [
                        new OA\Property(property: 'id', type: 'string', format: 'uuid'),
                        new OA\Property(property: 'collectionId', type: 'string', format: 'uuid'),
                        new OA\Property(property: 'libraryItemId', type: 'string', format: 'uuid'),
                        new OA\Property(property: 'createdAt', type: 'string', format: 'date-time'),
                    ],
                ),
            ),
            new OA\Response(
                response: 400,
                description: 'Invalid request',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse'),
            ),
            new OA\Response(
                response: 409,
                description: 'Library item already assigned to collection',
                content: new OA\JsonContent(
                    required: ['error'],
                    properties: [
                        new OA\Property(
                            property: 'error',
                            type: 'string',
                            example: 'Library item already assigned to collection',
                        ),
                    ],
                ),
            ),
        ],
    )]
    #[Route(
        '/api/collections/{collectionId}/items',
        name: 'api_collections_items_assign',
        methods: ['POST'],
    )]
    public function __invoke(
        string $collectionId,
        Request $request,
        AssignLibraryItemToCollectionHandler $handler,
    ): JsonResponse {
        try {
            new CollectionId($collectionId);
        } catch (InvalidCollectionException) {
            return $this->invalidRequestResponse();
        }

        $payload = json_decode($request->getContent(), true);

        if (!is_array($payload)) {
            return $this->invalidRequestResponse();
        }

        try {
            $assignRequest = AssignLibraryItemToCollectionRequest::fromArray($payload);
        } catch (InvalidCollectionRequestException) {
            return $this->invalidRequestResponse();
        }

        try {
            $result = $handler(new AssignLibraryItemToCollectionCommand(
                collectionId: $collectionId,
                libraryItemId: $assignRequest->libraryItemId,
            ));
        } catch (InvalidLibraryItemException) {
            return $this->invalidRequestResponse();
        } catch (CollectionItemAlreadyExistsException) {
            return $this->conflictResponse();
        }

        return $this->json(
            AssignLibraryItemToCollectionResponse::fromResult($result)->toArray(),
            Response::HTTP_CREATED,
        );
    }

    private function invalidRequestResponse(): JsonResponse
    {
        return $this->json(['error' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
    }

    private function conflictResponse(): JsonResponse
    {
        return $this->json(
            ['error' => 'Library item already assigned to collection'],
            Response::HTTP_CONFLICT,
        );
    }
}
