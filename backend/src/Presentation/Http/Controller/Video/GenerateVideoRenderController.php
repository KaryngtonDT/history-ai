<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Video;

use App\Application\VideoRender\Commands\GenerateVideoRenderCommand;
use App\Application\VideoRender\Handlers\GenerateVideoRenderHandler;
use App\Domain\VideoRender\Exception\InvalidVideoRenderException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GenerateVideoRenderController extends AbstractController
{
    #[Route('/api/videos/{videoId}/render', name: 'api_videos_render_generate', methods: ['POST'])]
    public function __invoke(string $videoId, Request $request, GenerateVideoRenderHandler $handler): JsonResponse
    {
        /** @var array<string, mixed>|null $payload */
        $payload = json_decode($request->getContent(), true);

        if (!is_array($payload)) {
            $payload = [];
        }

        $targetLanguages = $payload['targetLanguages'] ?? [];
        $provider = $payload['provider'] ?? null;
        $format = $payload['format'] ?? null;
        $quality = $payload['quality'] ?? null;

        if (!is_array($targetLanguages)) {
            return $this->json(['error' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
        }

        /** @var list<string> $languageCodes */
        $languageCodes = array_values(array_filter(
            array_map(
                static fn (mixed $value): ?string => is_string($value) ? $value : null,
                $targetLanguages,
            ),
            static fn (?string $value): bool => null !== $value && '' !== trim($value),
        ));

        try {
            $handler(new GenerateVideoRenderCommand(
                videoId: $videoId,
                targetLanguages: $languageCodes,
                provider: is_string($provider) ? $provider : null,
                format: is_string($format) ? $format : null,
                quality: is_string($quality) ? $quality : null,
            ));
        } catch (InvalidVideoRenderException) {
            return $this->json(['error' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
        }

        return $this->json(['status' => 'rendered'], Response::HTTP_ACCEPTED);
    }
}
