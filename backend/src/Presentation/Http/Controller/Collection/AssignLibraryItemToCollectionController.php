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
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AssignLibraryItemToCollectionController extends AbstractController
{
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
