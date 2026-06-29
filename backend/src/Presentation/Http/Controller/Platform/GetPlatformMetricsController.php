<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Platform;

use App\Application\Platform\PerformanceMetricsReaderInterface;
use App\Presentation\Http\Response\Platform\PlatformMetricsResponse;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GetPlatformMetricsController extends AbstractController
{
    private const int DEFAULT_LIMIT = 20;
    private const int MAX_LIMIT = 100;

    #[OA\Get(
        operationId: 'getPlatformMetrics',
        summary: 'Read recent platform performance metric snapshots',
        description: 'Internal diagnostic endpoint returning recent RAG/chat pipeline timings captured in memory. Not for public clients.',
        tags: ['Platform'],
        parameters: [
            new OA\Parameter(
                name: 'limit',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 100, example: 20),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Recent performance metric snapshots (newest first)',
                content: new OA\JsonContent(ref: '#/components/schemas/PlatformMetricsResponse'),
            ),
            new OA\Response(
                response: 400,
                description: 'Invalid limit',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse'),
            ),
        ],
    )]
    #[Route(
        '/internal/platform/metrics',
        name: 'internal_platform_metrics',
        methods: ['GET'],
    )]
    public function __invoke(Request $request, PerformanceMetricsReaderInterface $reader): JsonResponse
    {
        $limitResult = $this->resolveLimit($request);

        if ($limitResult instanceof JsonResponse) {
            return $limitResult;
        }

        return $this->json(
            PlatformMetricsResponse::fromCollection($reader->recent($limitResult)),
            Response::HTTP_OK,
        );
    }

    private function resolveLimit(Request $request): int|JsonResponse
    {
        if (!$request->query->has('limit')) {
            return self::DEFAULT_LIMIT;
        }

        $rawLimit = $request->query->get('limit');

        if (!is_scalar($rawLimit) || !ctype_digit((string) $rawLimit)) {
            return $this->json(['error' => 'Invalid limit'], Response::HTTP_BAD_REQUEST);
        }

        $limit = (int) $rawLimit;

        if ($limit < 1 || $limit > self::MAX_LIMIT) {
            return $this->json(['error' => 'Invalid limit'], Response::HTTP_BAD_REQUEST);
        }

        return $limit;
    }
}
