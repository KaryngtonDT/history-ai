<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Processing\Internal;

use App\Application\Processing\Commands\CompleteProcessingJobCommand;
use App\Application\Processing\Handlers\CompleteProcessingJobHandler;
use App\Domain\Processing\Exception\InvalidProcessingJobException;
use App\Domain\Processing\Exception\ProcessingJobNotFoundException;
use App\Domain\Processing\ProcessingJobId;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CompleteProcessingJobInternalController extends AbstractController
{
    #[Route(
        '/internal/processing-jobs/{id}/complete',
        name: 'internal_processing_jobs_complete',
        methods: ['POST'],
    )]
    public function __invoke(string $id, CompleteProcessingJobHandler $handler): JsonResponse
    {
        try {
            new ProcessingJobId($id);
        } catch (InvalidProcessingJobException) {
            return $this->invalidRequestResponse();
        }

        try {
            $handler(new CompleteProcessingJobCommand($id));
        } catch (ProcessingJobNotFoundException) {
            return $this->notFoundResponse();
        } catch (InvalidProcessingJobException) {
            return $this->conflictResponse();
        }

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    private function invalidRequestResponse(): JsonResponse
    {
        return $this->json(['error' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
    }

    private function notFoundResponse(): JsonResponse
    {
        return $this->json(
            ['error' => 'Processing job not found'],
            Response::HTTP_NOT_FOUND,
        );
    }

    private function conflictResponse(): JsonResponse
    {
        return $this->json(['error' => 'Invalid processing transition'], Response::HTTP_CONFLICT);
    }
}
