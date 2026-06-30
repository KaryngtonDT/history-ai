<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\History;

use App\Application\History\Commands\ReprocessExecutionCommand;
use App\Application\History\ReprocessExecutionHandler;
use App\Domain\History\Exception\InvalidExecutionHistoryException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ReprocessVideoHistoryController extends AbstractController
{
    #[Route('/api/videos/{videoId}/history/{version}/reprocess', name: 'api_videos_history_reprocess', methods: ['POST'], requirements: ['version' => '\d+'])]
    public function __invoke(string $videoId, int $version, Request $request, ReprocessExecutionHandler $handler): JsonResponse
    {
        /** @var mixed $payload */
        $payload = json_decode($request->getContent(), true);
        $overrides = is_array($payload['providerOverrides'] ?? null) ? $payload['providerOverrides'] : [];
        $providerOverrides = [];

        foreach ($overrides as $stage => $providerId) {
            if (is_string($stage) && is_string($providerId) && '' !== trim($providerId)) {
                $providerOverrides[$stage] = $providerId;
            }
        }

        $batchJobId = is_string($payload['batchJobId'] ?? null) ? $payload['batchJobId'] : null;

        try {
            $handler(new ReprocessExecutionCommand($videoId, $version, $providerOverrides, $batchJobId));
        } catch (InvalidExecutionHistoryException $exception) {
            return $this->json(['error' => $exception->getMessage()], Response::HTTP_NOT_FOUND);
        }

        return $this->json(['status' => 'queued'], Response::HTTP_ACCEPTED);
    }
}
