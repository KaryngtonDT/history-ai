<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\ShadowVoice;

use App\Application\ShadowVoice\ShadowVoiceStudio;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class PostShadowVoicePreviewController extends AbstractController
{
    #[Route('/api/shadow/voice/preview', name: 'api_shadow_voice_preview', methods: ['POST'])]
    public function __invoke(Request $request, ShadowVoiceStudio $studio): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);

        if (!is_array($payload)) {
            return $this->json(['error' => 'Invalid JSON payload.'], 400);
        }

        $voiceId = is_string($payload['voiceId'] ?? null) ? $payload['voiceId'] : 'browser-default';
        $parameters = is_array($payload['parameters'] ?? null) ? $payload['parameters'] : [];

        return $this->json($studio->preview($voiceId, $parameters));
    }
}
