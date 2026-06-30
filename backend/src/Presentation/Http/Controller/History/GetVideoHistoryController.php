<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\History;

use App\Application\History\GetExecutionHistoryHandler;
use App\Application\History\Queries\GetExecutionHistoryQuery;
use App\Domain\History\Exception\InvalidExecutionHistoryException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GetVideoHistoryController extends AbstractController
{
    #[Route('/api/videos/{videoId}/history', name: 'api_videos_history_list', methods: ['GET'])]
    public function __invoke(string $videoId, GetExecutionHistoryHandler $handler): JsonResponse
    {
        try {
            $result = $handler(new GetExecutionHistoryQuery($videoId));
        } catch (InvalidExecutionHistoryException) {
            return $this->json(['error' => 'Execution history not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json([
            'id' => $result->id,
            'videoId' => $result->videoId,
            'versions' => array_map(
                static fn ($version): array => HistoryResponseFactory::versionFromResult($version),
                $result->versions,
            ),
        ]);
    }
}
