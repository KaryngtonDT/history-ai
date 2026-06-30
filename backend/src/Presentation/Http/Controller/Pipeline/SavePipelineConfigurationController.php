<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Pipeline;

use App\Application\Pipeline\Commands\SavePipelineConfigurationCommand;
use App\Application\Pipeline\Handlers\SavePipelineConfigurationHandler;
use App\Domain\Pipeline\Exception\InvalidPipelineConfigurationException;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SavePipelineConfigurationController extends AbstractController
{
    #[OA\Put(
        operationId: 'savePipelineConfiguration',
        summary: 'Save pipeline configuration',
        description: 'Persists the selected AI provider for each pipeline stage.',
        tags: ['Pipeline'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/SavePipelineConfigurationRequest'),
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Saved pipeline configuration',
                content: new OA\JsonContent(ref: '#/components/schemas/PipelineConfiguration'),
            ),
            new OA\Response(
                response: 400,
                description: 'Invalid request',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse'),
            ),
        ],
    )]
    #[Route('/api/pipeline', name: 'api_pipeline_save', methods: ['PUT'])]
    public function __invoke(Request $request, SavePipelineConfigurationHandler $handler): JsonResponse
    {
        /** @var array<string, mixed>|null $payload */
        $payload = json_decode($request->getContent(), true);

        if (!is_array($payload)) {
            return $this->json(['error' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
        }

        $stages = $payload['stages'] ?? null;

        if (!is_array($stages)) {
            return $this->json(['error' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
        }

        /** @var list<array{stage: string, providerId: string}> $normalizedStages */
        $normalizedStages = [];

        foreach ($stages as $stage) {
            if (!is_array($stage)) {
                return $this->json(['error' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
            }

            $stageValue = $stage['stage'] ?? null;
            $providerId = $stage['providerId'] ?? null;

            if (!is_string($stageValue) || !is_string($providerId)) {
                return $this->json(['error' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
            }

            $normalizedStages[] = [
                'stage' => $stageValue,
                'providerId' => $providerId,
            ];
        }

        try {
            $result = $handler(new SavePipelineConfigurationCommand($normalizedStages));
        } catch (InvalidPipelineConfigurationException) {
            return $this->json(['error' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
        }

        return $this->json([
            'id' => $result->id,
            'version' => $result->version,
            'createdAt' => $result->createdAt,
            'updatedAt' => $result->updatedAt,
            'stages' => $result->stages,
        ]);
    }
}
