<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\History;

use App\Application\History\CompareExecutionHandler;
use App\Application\History\Queries\CompareExecutionQuery;
use App\Domain\History\Exception\InvalidExecutionHistoryException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CompareVideoHistoryController extends AbstractController
{
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
