<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Video;

use App\Application\VoiceClone\Commands\GenerateVideoVoiceCloneCommand;
use App\Application\VoiceClone\Handlers\GenerateVideoVoiceCloneHandler;
use App\Domain\VoiceClone\Exception\InvalidVoiceCloneException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GenerateVideoVoiceCloneController extends AbstractController
{
    #[Route('/api/videos/{videoId}/voice-clone', name: 'api_videos_voice_clone_generate', methods: ['POST'])]
    public function __invoke(string $videoId, Request $request, GenerateVideoVoiceCloneHandler $handler): JsonResponse
    {
        /** @var array<string, mixed>|null $payload */
        $payload = json_decode($request->getContent(), true);

        if (!is_array($payload)) {
            return $this->json(['error' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
        }

        $targetLanguages = $payload['targetLanguages'] ?? [];
        $provider = $payload['provider'] ?? null;
        $voiceMode = $payload['voiceMode'] ?? null;

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
            $handler(new GenerateVideoVoiceCloneCommand(
                videoId: $videoId,
                targetLanguages: $languageCodes,
                provider: is_string($provider) ? $provider : null,
                voiceMode: is_string($voiceMode) ? $voiceMode : null,
            ));
        } catch (InvalidVoiceCloneException) {
            return $this->json(['error' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
        }

        return $this->json(['status' => 'generated'], Response::HTTP_ACCEPTED);
    }
}
