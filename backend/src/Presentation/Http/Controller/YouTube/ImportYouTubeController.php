<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\YouTube;

use App\Application\YouTube\Commands\ImportYouTubeCommand;
use App\Application\YouTube\Handlers\ImportYouTubeHandler;
use App\Domain\Orchestrator\ProcessingMode;
use App\Domain\Orchestrator\ProcessingStrategy;
use App\Domain\YouTube\Exception\InvalidYouTubeException;
use App\Presentation\Http\Response\YouTube\ImportYouTubeResponse;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ImportYouTubeController extends AbstractController
{
    #[OA\Post(
        operationId: 'importYouTube',
        summary: 'Import YouTube video',
        description: 'Downloads a YouTube video, creates a VideoJob, registers a Source, and queues the standard video pipeline.',
        tags: ['YouTube'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['url'],
                properties: [
                    new OA\Property(property: 'url', type: 'string', example: 'https://www.youtube.com/watch?v=dQw4w9WgXcQ'),
                    new OA\Property(property: 'processingMode', type: 'string', enum: ['manual', 'automatic']),
                    new OA\Property(property: 'strategy', type: 'string', nullable: true),
                ],
            ),
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'YouTube video imported',
                content: new OA\JsonContent(ref: '#/components/schemas/ImportYouTubeResponse'),
            ),
            new OA\Response(
                response: 400,
                description: 'Invalid URL or import failed',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse'),
            ),
        ],
    )]
    #[Route('/api/youtube', name: 'api_youtube_import', methods: ['POST'])]
    public function __invoke(Request $request, ImportYouTubeHandler $handler): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);

        if (!is_array($payload) || !is_string($payload['url'] ?? null)) {
            return $this->json(['error' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $result = $handler(new ImportYouTubeCommand(
                url: trim($payload['url']),
                processingMode: $this->parseProcessingMode($payload['processingMode'] ?? null),
                strategy: $this->parseStrategy($payload['strategy'] ?? null),
            ));
        } catch (InvalidYouTubeException $exception) {
            return $this->json(['error' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return $this->json(
            ImportYouTubeResponse::fromResult($result)->toArray(),
            Response::HTTP_CREATED,
        );
    }

    private function parseProcessingMode(mixed $value): ProcessingMode
    {
        if (!is_string($value)) {
            return ProcessingMode::Manual;
        }

        return ProcessingMode::tryFrom($value) ?? ProcessingMode::Manual;
    }

    private function parseStrategy(mixed $value): ?ProcessingStrategy
    {
        if (!is_string($value) || '' === trim($value)) {
            return null;
        }

        return ProcessingStrategy::tryFrom($value);
    }
}
