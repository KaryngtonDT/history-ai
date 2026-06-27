<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Collection;

use App\Application\Collection\Commands\CreateCollectionCommand;
use App\Application\Collection\Handlers\CreateCollectionHandler;
use App\Domain\Collection\Exception\InvalidCollectionNameException;
use App\Presentation\Http\Request\Collection\CreateCollectionRequest;
use App\Presentation\Http\Request\Collection\Exception\InvalidCollectionRequestException;
use App\Presentation\Http\Response\Collection\CreateCollectionResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CreateCollectionController extends AbstractController
{
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
