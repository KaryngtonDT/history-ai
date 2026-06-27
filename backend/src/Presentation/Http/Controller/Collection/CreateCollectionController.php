<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Collection;

use App\Application\Collection\Commands\CreateCollectionCommand;
use App\Application\Collection\Handlers\CreateCollectionHandler;
use App\Domain\Collection\Exception\InvalidCollectionNameException;
use App\Presentation\Http\Request\Collection\CreateCollectionRequest;
use App\Presentation\Http\Request\Collection\Exception\InvalidCollectionRequestException;
use App\Presentation\Http\Response\Collection\CreateCollectionResponse;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CreateCollectionController extends AbstractController
{
    #[OA\Post(
        operationId: 'createCollection',
        summary: 'Create collection',
        description: 'Creates a themed collection to group library items.',
        tags: ['Collections'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Ancient Rome'),
                    new OA\Property(property: 'description', type: 'string', example: 'Resources about the Roman Empire', nullable: true),
                ],
            ),
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Collection created',
                content: new OA\JsonContent(
                    required: ['id', 'name', 'description', 'createdAt'],
                    properties: [
                        new OA\Property(property: 'id', type: 'string', format: 'uuid'),
                        new OA\Property(property: 'name', type: 'string', example: 'Ancient Rome'),
                        new OA\Property(property: 'description', type: 'string', nullable: true),
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
    #[Route('/api/collections', name: 'api_collections_create', methods: ['POST'])]
    public function __invoke(Request $request, CreateCollectionHandler $handler): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);

        if (!is_array($payload)) {
            return $this->invalidRequestResponse();
        }

        try {
            $createRequest = CreateCollectionRequest::fromArray($payload);
        } catch (InvalidCollectionRequestException) {
            return $this->invalidRequestResponse();
        }

        try {
            $result = $handler(new CreateCollectionCommand(
                name: $createRequest->name,
                description: $createRequest->description,
            ));
        } catch (InvalidCollectionNameException) {
            return $this->invalidRequestResponse();
        }

        return $this->json(
            CreateCollectionResponse::fromResult($result)->toArray(),
            Response::HTTP_CREATED,
        );
    }

    private function invalidRequestResponse(): JsonResponse
    {
        return $this->json(['error' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
    }
}
