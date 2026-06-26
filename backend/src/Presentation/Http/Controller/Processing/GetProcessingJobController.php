<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Processing;

use App\Application\Processing\Handlers\GetProcessingJobHandler;
use App\Application\Processing\Queries\GetProcessingJobQuery;
use App\Domain\Processing\Exception\InvalidProcessingJobException;
use App\Domain\Processing\ProcessingJobId;
use App\Presentation\Http\Response\Processing\GetProcessingJobResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GetProcessingJobController extends AbstractController
{
    #[Route(
        '/api/processing-jobs/{id}',
        name: 'api_processing_jobs_get',
        methods: ['GET'],
    )]
    public function __invoke(string $id, GetProcessingJobHandler $handler): JsonResponse
    {
        try {
            new ProcessingJobId($id);
        } catch (InvalidProcessingJobException) {
            return $this->json(['error' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
        }

        $result = $handler(new GetProcessingJobQuery($id));

        if (null === $result) {
            return $this->json(
                ['error' => 'Processing job not found'],
                Response::HTTP_NOT_FOUND,
            );
        }

        return $this->json(GetProcessingJobResponse::fromResult($result)->toArray());
    }
}
