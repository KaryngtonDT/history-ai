<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\History;

use App\Application\History\Commands\ReprocessExecutionCommand;
use App\Application\History\ReprocessExecutionHandler;
use App\Domain\Collaboration\Exception\InvalidWorkspaceMemberException;
use App\Domain\History\Exception\InvalidExecutionHistoryException;
use App\Presentation\Http\CollaboratorResolver;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ReprocessVideoHistoryController extends AbstractController
{
    #[OA\Post(
        operationId: 'reprocessVideoHistory',
        summary: 'Reprocess a video from a previous execution version',
        tags: ['Execution History'],
        parameters: [
            new OA\Parameter(name: 'videoId', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
            new OA\Parameter(name: 'version', in: 'path', required: true, schema: new OA\Schema(type: 'integer', minimum: 1)),
        ],
        requestBody: new OA\RequestBody(
            required: false,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'providerOverrides',
                        type: 'object',
                        additionalProperties: new OA\AdditionalProperties(type: 'string'),
                    ),
                    new OA\Property(property: 'batchJobId', type: 'string', format: 'uuid', nullable: true),
                ],
            ),
        ),
        responses: [
            new OA\Response(response: 202, description: 'Reprocessing queued'),
            new OA\Response(response: 404, description: 'Version not found', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
        ],
    )]
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
            $collaborator = CollaboratorResolver::fromRequest($request);
            $handler(new ReprocessExecutionCommand(
                $videoId,
                $version,
                $providerOverrides,
                $batchJobId,
                $collaborator->userId,
            ));
        } catch (InvalidWorkspaceMemberException $exception) {
            return $this->json(['error' => $exception->getMessage()], Response::HTTP_FORBIDDEN);
        } catch (InvalidExecutionHistoryException $exception) {
            return $this->json(['error' => $exception->getMessage()], Response::HTTP_NOT_FOUND);
        }

        return $this->json(['status' => 'queued'], Response::HTTP_ACCEPTED);
    }
}
