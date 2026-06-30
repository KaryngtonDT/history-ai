<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Project;

use App\Application\Workspace\Commands\ProcessProjectCommand;
use App\Application\Workspace\Handlers\ProcessProjectHandler;
use App\Domain\Orchestrator\ProcessingMode;
use App\Domain\Orchestrator\ProcessingStrategy;
use App\Domain\Workspace\Exception\InvalidProjectException;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ProcessProjectController extends AbstractController
{
    #[OA\Post(
        operationId: 'processProject',
        summary: 'Process all videos in a project',
        tags: ['Projects'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['targetLanguages'],
                properties: [
                    new OA\Property(
                        property: 'targetLanguages',
                        type: 'array',
                        items: new OA\Items(type: 'string'),
                        example: ['fr', 'de'],
                    ),
                    new OA\Property(property: 'processingMode', type: 'string', example: 'automatic'),
                    new OA\Property(property: 'strategy', type: 'string', nullable: true),
                ],
            ),
        ),
        responses: [
            new OA\Response(response: 202, description: 'Batch processing started', content: new OA\JsonContent(ref: '#/components/schemas/BatchJob')),
            new OA\Response(response: 404, description: 'Project not found', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
        ],
    )]
    #[Route('/api/projects/{id}/process', name: 'api_projects_process', methods: ['POST'])]
    public function __invoke(string $id, Request $request, ProcessProjectHandler $handler): JsonResponse
    {
        /** @var mixed $payload */
        $payload = json_decode($request->getContent(), true);
        $languages = is_array($payload['targetLanguages'] ?? null) ? $payload['targetLanguages'] : [];
        $languages = array_values(array_filter($languages, is_string(...)));

        if ([] === $languages) {
            return $this->json(['error' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
        }

        $modeValue = is_string($payload['processingMode'] ?? null) ? $payload['processingMode'] : 'automatic';
        $mode = ProcessingMode::tryFrom($modeValue) ?? ProcessingMode::Automatic;
        $strategyValue = is_string($payload['strategy'] ?? null) ? $payload['strategy'] : null;
        $strategy = null !== $strategyValue ? ProcessingStrategy::tryFrom($strategyValue) : null;

        try {
            $result = $handler(new ProcessProjectCommand($id, $languages, $mode, $strategy));
        } catch (InvalidProjectException $exception) {
            $status = str_contains($exception->getMessage(), 'not found')
                ? Response::HTTP_NOT_FOUND
                : Response::HTTP_BAD_REQUEST;

            return $this->json(['error' => $exception->getMessage()], $status);
        }

        return $this->json([
            'id' => $result->batchJobId,
            'projectId' => $result->projectId,
            'status' => $result->status,
            'progress' => $result->progress,
            'totalVideos' => $result->totalVideos,
            'queuedVideos' => $result->queuedVideos,
            'targetLanguages' => $result->targetLanguages,
            'failedVideoIds' => $result->failedVideoIds,
        ], Response::HTTP_ACCEPTED);
    }
}
