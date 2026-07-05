<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Runtime;

use App\Application\Runtime\RuntimePlatformInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/runtime')]
final class RuntimeController extends AbstractController
{
    public function __construct(private readonly RuntimePlatformInterface $platform)
    {
    }

    #[OA\Get(operationId: 'getRuntimeOverview', tags: ['Runtime'])]
    #[Route('', name: 'api_runtime_overview', methods: ['GET'])]
    public function overview(): JsonResponse
    {
        return $this->json($this->platform->overview());
    }

    #[OA\Get(operationId: 'getRuntimeReadiness', tags: ['Runtime'])]
    #[Route('/readiness', name: 'api_runtime_readiness', methods: ['GET'])]
    public function readiness(): JsonResponse
    {
        return $this->json($this->platform->readiness());
    }

    #[OA\Get(operationId: 'getRuntimeHealth', tags: ['Runtime'])]
    #[Route('/health', name: 'api_runtime_health', methods: ['GET'])]
    public function health(): JsonResponse
    {
        return $this->json($this->platform->health());
    }

    #[OA\Get(operationId: 'listRuntimeEngines', tags: ['Runtime'])]
    #[Route('/engines', name: 'api_runtime_engines', methods: ['GET'])]
    public function engines(): JsonResponse
    {
        return $this->json(['engines' => $this->platform->engines()]);
    }

    #[OA\Get(operationId: 'getRuntimeCatalog', tags: ['Runtime'])]
    #[Route('/catalog', name: 'api_runtime_catalog', methods: ['GET'])]
    public function catalog(): JsonResponse
    {
        return $this->json($this->platform->catalog());
    }

    #[OA\Get(operationId: 'getRuntimeRecommendations', tags: ['Runtime'])]
    #[Route('/recommendations', name: 'api_runtime_recommendations', methods: ['GET'])]
    public function recommendations(): JsonResponse
    {
        return $this->json(['recommendations' => $this->platform->recommendations()]);
    }

    #[OA\Get(operationId: 'getRuntimeProfiles', tags: ['Runtime'])]
    #[Route('/profiles', name: 'api_runtime_profiles', methods: ['GET'])]
    public function profiles(): JsonResponse
    {
        return $this->json(['profiles' => $this->platform->profiles()]);
    }

    #[OA\Post(operationId: 'testRuntimeEngine', tags: ['Runtime'])]
    #[Route('/engines/{id}/test', name: 'api_runtime_engine_test', methods: ['POST'])]
    public function testEngine(string $id): JsonResponse
    {
        return $this->json($this->platform->testEngine($id));
    }

    #[OA\Post(operationId: 'runRuntimeBenchmark', tags: ['Runtime'])]
    #[Route('/benchmark', name: 'api_runtime_benchmark', methods: ['POST'])]
    public function benchmark(Request $request): JsonResponse
    {
        /** @var array<string, mixed>|null $payload */
        $payload = json_decode($request->getContent(), true);
        $engineId = is_array($payload) && is_string($payload['engineId'] ?? null) ? $payload['engineId'] : null;

        return $this->json($this->platform->benchmark($engineId));
    }

    #[OA\Post(operationId: 'runRuntimeFullBenchmark', tags: ['Runtime'])]
    #[Route('/benchmark/full', name: 'api_runtime_benchmark_full', methods: ['POST'])]
    public function benchmarkFull(): JsonResponse
    {
        return $this->json($this->platform->benchmark());
    }

    #[OA\Post(operationId: 'validateRuntimePipeline', tags: ['Runtime'])]
    #[Route('/pipeline/validate', name: 'api_runtime_pipeline_validate', methods: ['POST'])]
    public function validatePipeline(): JsonResponse
    {
        return $this->json($this->platform->validatePipeline());
    }

    #[OA\Put(operationId: 'updateRuntimeProfile', tags: ['Runtime'])]
    #[Route('/profile', name: 'api_runtime_profile_update', methods: ['PUT'])]
    public function updateProfile(Request $request): JsonResponse
    {
        /** @var array<string, mixed> $payload */
        $payload = json_decode($request->getContent(), true) ?: [];

        return $this->json($this->platform->updateProfile($payload));
    }

    #[OA\Put(operationId: 'updateRuntimeSelection', tags: ['Runtime'])]
    #[Route('/selection', name: 'api_runtime_selection_update', methods: ['PUT'])]
    public function updateSelection(Request $request): JsonResponse
    {
        /** @var array<string, mixed> $payload */
        $payload = json_decode($request->getContent(), true) ?: [];

        return $this->json($this->platform->updateSelection($payload));
    }

    #[OA\Post(operationId: 'provisionRuntimeEngine', tags: ['Runtime'])]
    #[Route('/engines/{id}/provision', name: 'api_runtime_engine_provision', methods: ['POST'])]
    public function provisionEngine(string $id): JsonResponse
    {
        return $this->json($this->platform->provisionEngine($id));
    }

    #[OA\Post(operationId: 'provisionAllRuntimeEngines', tags: ['Runtime'])]
    #[Route('/provision', name: 'api_runtime_provision_all', methods: ['POST'])]
    public function provisionAll(): JsonResponse
    {
        return $this->json($this->platform->provisionAll());
    }

    #[OA\Get(operationId: 'getRuntimeReport', tags: ['Runtime'])]
    #[Route('/report/{pipelineId}', name: 'api_runtime_report', methods: ['GET'])]
    public function report(string $pipelineId): JsonResponse
    {
        $report = $this->platform->report($pipelineId);

        if (null === $report) {
            return $this->json(['error' => 'Report not found'], 404);
        }

        return $this->json($report);
    }
}
