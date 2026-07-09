<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Pipeline;

use App\Application\Pipeline\Handlers\CancelPipelineStageHandler;
use App\Application\Pipeline\Handlers\ContinuePipelineStageHandler;
use App\Application\Pipeline\Handlers\GetPipelineEventsHandler;
use App\Application\Pipeline\Handlers\GetPipelineJobsHandler;
use App\Application\Pipeline\Handlers\StartPipelineStageHandler;
use App\Application\Pipeline\Handlers\SubmitPipelineChoiceHandler;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PipelineJobsController extends AbstractController
{
    #[OA\Get(
        operationId: 'getPipelineJobs',
        summary: 'Get pipeline jobs for a source',
        tags: ['Pipeline'],
    )]
    #[Route('/api/pipeline/jobs/{sourceId}', name: 'api_pipeline_jobs_get', methods: ['GET'])]
    public function getJobs(string $sourceId, GetPipelineJobsHandler $handler): JsonResponse
    {
        return $this->json($handler->forSource($sourceId));
    }

    #[OA\Get(
        operationId: 'getPipelineEvents',
        summary: 'Get pipeline events and notifications for a source',
        tags: ['Pipeline'],
    )]
    #[Route('/api/pipeline/jobs/{sourceId}/events', name: 'api_pipeline_jobs_events', methods: ['GET'], priority: 10)]
    public function events(string $sourceId, GetPipelineEventsHandler $handler): JsonResponse
    {
        return $this->json($handler($sourceId));
    }

    #[OA\Get(
        operationId: 'getPipelineJobStage',
        summary: 'Get pipeline job for a source stage',
        tags: ['Pipeline'],
    )]
    #[Route('/api/pipeline/jobs/{sourceId}/{stage}', name: 'api_pipeline_jobs_stage_get', methods: ['GET'])]
    public function getStage(string $sourceId, string $stage, GetPipelineJobsHandler $handler): JsonResponse
    {
        $result = $handler->forSourceStage($sourceId, $stage);

        if (null === $result) {
            return $this->json(['error' => 'Pipeline job not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($result);
    }

    #[OA\Post(
        operationId: 'startPipelineStage',
        summary: 'Start or resume a pipeline stage',
        tags: ['Pipeline'],
    )]
    #[Route('/api/pipeline/jobs/{sourceId}/{stage}/start', name: 'api_pipeline_jobs_stage_start', methods: ['POST'])]
    public function start(
        string $sourceId,
        string $stage,
        Request $request,
        StartPipelineStageHandler $handler,
    ): JsonResponse {
        /** @var array<string, mixed> $payload */
        $payload = json_decode($request->getContent() ?: '{}', true) ?? [];

        return $this->json($handler(
            $sourceId,
            $stage,
            (bool) ($payload['forceRestart'] ?? false),
            is_array($payload['metadata'] ?? null) ? $payload['metadata'] : (is_array($payload['stageMetadata'] ?? null) ? $payload['stageMetadata'] : []),
        ), Response::HTTP_ACCEPTED);
    }

    #[OA\Post(
        operationId: 'cancelPipelineStage',
        summary: 'Cancel a pipeline stage job',
        tags: ['Pipeline'],
    )]
    #[Route('/api/pipeline/jobs/{sourceId}/{stage}/cancel', name: 'api_pipeline_jobs_stage_cancel', methods: ['POST'])]
    public function cancel(
        string $sourceId,
        string $stage,
        Request $request,
        CancelPipelineStageHandler $handler,
    ): JsonResponse {
        /** @var array<string, mixed> $payload */
        $payload = json_decode($request->getContent() ?: '{}', true) ?? [];
        $reason = is_string($payload['reason'] ?? null) ? $payload['reason'] : 'Cancelled by user';

        return $this->json($handler($sourceId, $stage, $reason));
    }

    #[OA\Post(
        operationId: 'continuePipelineStage',
        summary: 'Confirm stage completion and prepare next stage',
        tags: ['Pipeline'],
    )]
    #[Route('/api/pipeline/jobs/{sourceId}/{stage}/continue', name: 'api_pipeline_jobs_stage_continue', methods: ['POST'])]
    public function continueStage(
        string $sourceId,
        string $stage,
        ContinuePipelineStageHandler $handler,
    ): JsonResponse {
        return $this->json($handler($sourceId, $stage));
    }

    #[OA\Post(
        operationId: 'submitPipelineChoice',
        summary: 'Submit user choice for a pipeline stage',
        tags: ['Pipeline'],
    )]
    #[Route('/api/pipeline/jobs/{sourceId}/{stage}/choice', name: 'api_pipeline_jobs_stage_choice', methods: ['POST'])]
    public function choice(
        string $sourceId,
        string $stage,
        Request $request,
        SubmitPipelineChoiceHandler $handler,
    ): JsonResponse {
        /** @var array<string, mixed> $payload */
        $payload = json_decode($request->getContent() ?: '{}', true) ?? [];
        $choice = is_string($payload['choice'] ?? null) ? $payload['choice'] : '';

        if ('' === $choice) {
            return $this->json(['error' => 'choice is required'], Response::HTTP_BAD_REQUEST);
        }

        return $this->json($handler($sourceId, $stage, $choice));
    }
}
