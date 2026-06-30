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

final class GetVideoHistoryVersionController extends AbstractController
{
    #[Route('/api/videos/{videoId}/history/{version}', name: 'api_videos_history_version', methods: ['GET'], requirements: ['version' => '\d+'])]
    public function __invoke(string $videoId, int $version, GetExecutionHistoryHandler $handler): JsonResponse
    {
        try {
            $result = $handler(new GetExecutionHistoryQuery($videoId));
        } catch (InvalidExecutionHistoryException) {
            return $this->json(['error' => 'Execution history not found'], Response::HTTP_NOT_FOUND);
        }

        foreach ($result->versions as $entry) {
            if ($entry->versionNumber === $version) {
                return $this->json(HistoryResponseFactory::versionFromResult($entry));
            }
        }

        return $this->json(['error' => 'Execution version not found'], Response::HTTP_NOT_FOUND);
    }
}
