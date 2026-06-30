<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\History;

use App\Application\History\CompareExecutionHandler;
use App\Application\History\Queries\CompareExecutionQuery;
use App\Domain\History\Exception\InvalidExecutionHistoryException;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CompareVideoHistoryController extends AbstractController
{
    #[OA\Get(
        operationId: 'compareVideoHistory',
        summary: 'Compare two execution versions',
        tags: ['Execution History'],
        parameters: [
            new OA\Parameter(name: 'videoId', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
            new OA\Parameter(name: 'left', in: 'query', required: true, schema: new OA\Schema(type: 'integer', minimum: 1)),
            new OA\Parameter(name: 'right', in: 'query', required: true, schema: new OA\Schema(type: 'integer', minimum: 1)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Comparison result', content: new OA\JsonContent(ref: '#/components/schemas/ComparisonResult')),
            new OA\Response(response: 404, description: 'Version not found', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
        ],
    )]
    #[Route('/api/videos/{videoId}/history/compare', name: 'api_videos_history_compare', methods: ['GET'])]
    public function __invoke(string $videoId, Request $request, CompareExecutionHandler $handler): JsonResponse
    {
        $leftVersion = (int) $request->query->get('left', '0');
        $rightVersion = (int) $request->query->get('right', '0');

        if ($leftVersion < 1 || $rightVersion < 1) {
            return $this->json(['error' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $result = $handler(new CompareExecutionQuery($videoId, $leftVersion, $rightVersion));
        } catch (InvalidExecutionHistoryException) {
            return $this->json(['error' => 'Execution version not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json(HistoryResponseFactory::comparisonFromResult($result));
    }
}
