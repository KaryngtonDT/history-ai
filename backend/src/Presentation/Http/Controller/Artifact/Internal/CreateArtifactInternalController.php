<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Artifact\Internal;

use App\Application\Artifact\Commands\CreateArtifactCommand;
use App\Application\Artifact\Handlers\CreateArtifactHandler;
use App\Domain\Artifact\Exception\InvalidArtifactException;
use App\Presentation\Http\Request\Artifact\CreateArtifactRequest;
use App\Presentation\Http\Request\Artifact\Exception\InvalidArtifactRequestException;
use App\Presentation\Http\Response\Artifact\CreateArtifactResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CreateArtifactInternalController extends AbstractController
{
    #[Route(
        '/internal/artifacts',
        name: 'internal_artifacts_create',
        methods: ['POST'],
    )]
    public function __invoke(Request $request, CreateArtifactHandler $handler): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);

        if (!is_array($payload)) {
            return $this->invalidRequestResponse();
        }

        try {
            $createRequest = CreateArtifactRequest::fromArray($payload);
        } catch (InvalidArtifactRequestException) {
            return $this->invalidRequestResponse();
        }

        try {
            $result = $handler(new CreateArtifactCommand(
                contentId: $createRequest->contentId,
                processingJobId: $createRequest->processingJobId,
                artifactType: $createRequest->type,
                artifactContent: $createRequest->content,
            ));
        } catch (InvalidArtifactException) {
            return $this->invalidRequestResponse();
        }

        return $this->json(
            CreateArtifactResponse::fromResult($result)->toArray(),
            Response::HTTP_CREATED,
        );
    }

    private function invalidRequestResponse(): JsonResponse
    {
        return $this->json(['error' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
    }
}
