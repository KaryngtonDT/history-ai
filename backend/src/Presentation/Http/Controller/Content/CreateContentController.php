<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Content;

use App\Application\Content\Commands\CreateContentCommand;
use App\Application\Content\Handlers\CreateContentHandler;
use App\Domain\Content\Exception\InvalidContentTitleException;
use App\Presentation\Http\Request\Content\CreateContentRequest;
use App\Presentation\Http\Request\Content\Exception\InvalidContentRequestException;
use App\Presentation\Http\Response\Content\CreateContentResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CreateContentController extends AbstractController
{
    #[Route('/api/contents', name: 'api_contents_create', methods: ['POST'])]
    public function __invoke(Request $request, CreateContentHandler $handler): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);

        if (!is_array($payload)) {
            return $this->invalidRequestResponse();
        }

        try {
            $createRequest = CreateContentRequest::fromArray($payload);
        } catch (InvalidContentRequestException) {
            return $this->invalidRequestResponse();
        }

        try {
            $result = $handler(new CreateContentCommand(
                title: $createRequest->title,
                sourceType: $createRequest->sourceType,
            ));
        } catch (InvalidContentTitleException) {
            return $this->invalidRequestResponse();
        }

        return $this->json(
            CreateContentResponse::fromContentId($result->contentId)->toArray(),
            Response::HTTP_CREATED,
        );
    }

    private function invalidRequestResponse(): JsonResponse
    {
        return $this->json(['error' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
    }
}
