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
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AddLibraryItemController extends AbstractController
{
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
