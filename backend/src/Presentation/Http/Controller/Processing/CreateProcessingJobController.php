<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Processing;

use App\Application\Processing\Commands\CreateProcessingJobCommand;
use App\Application\Processing\Handlers\CreateProcessingJobHandler;
use App\Domain\Content\ContentId;
use App\Domain\Content\Exception\InvalidContentIdException;
use App\Presentation\Http\Request\Processing\CreateProcessingJobRequest;
use App\Presentation\Http\Request\Processing\Exception\InvalidProcessingRequestException;
use App\Presentation\Http\Response\Processing\CreateProcessingJobResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CreateProcessingJobController extends AbstractController
{
    #[Route(
        '/api/contents/{contentId}/processing-jobs',
        name: 'api_contents_processing_jobs_create',
        methods: ['POST'],
    )]
    public function __invoke(
        string $contentId,
        Request $request,
        CreateProcessingJobHandler $handler,
    ): JsonResponse {
        try {
            new ContentId($contentId);
        } catch (InvalidContentIdException) {
            return $this->invalidRequestResponse();
        }

        $payload = json_decode($request->getContent(), true);

        if (!is_array($payload)) {
            return $this->invalidRequestResponse();
        }

        try {
            $createRequest = CreateProcessingJobRequest::fromArray($payload);
        } catch (InvalidProcessingRequestException) {
            return $this->invalidRequestResponse();
        }

        $result = $handler(new CreateProcessingJobCommand(
            contentId: $contentId,
            type: $createRequest->type,
        ));

        return $this->json(
            CreateProcessingJobResponse::fromResult($result)->toArray(),
            Response::HTTP_CREATED,
        );
    }

    private function invalidRequestResponse(): JsonResponse
    {
        return $this->json(['error' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
    }
}
