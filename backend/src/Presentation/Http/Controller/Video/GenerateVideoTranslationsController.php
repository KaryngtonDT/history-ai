<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Video;

use App\Application\Translation\Commands\GenerateVideoTranslationsCommand;
use App\Application\Translation\Handlers\GenerateVideoTranslationsHandler;
use App\Domain\Translation\Exception\InvalidTranslationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GenerateVideoTranslationsController extends AbstractController
{
    #[Route('/api/videos/{videoId}/translations', name: 'api_videos_translations_generate', methods: ['POST'])]
    public function __invoke(string $videoId, Request $request, GenerateVideoTranslationsHandler $handler): JsonResponse
    {
        /** @var array<string, mixed>|null $payload */
        $payload = json_decode($request->getContent(), true);

        if (!is_array($payload)) {
            return $this->json(['error' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
        }

        $targetLanguages = $payload['targetLanguages'] ?? null;
        $provider = $payload['provider'] ?? null;

        if (!is_array($targetLanguages) || [] === $targetLanguages) {
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
            $handler(new GenerateVideoTranslationsCommand(
                videoId: $videoId,
                targetLanguages: $languageCodes,
                provider: is_string($provider) ? $provider : null,
            ));
        } catch (InvalidTranslationException) {
            return $this->json(['error' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
        }

        return $this->json(['status' => 'generated'], Response::HTTP_ACCEPTED);
    }
}
