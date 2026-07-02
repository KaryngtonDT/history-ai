<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\ShadowVoice;

use App\Application\ShadowVoice\ShadowVoicePreset;
use App\Application\ShadowVoice\ShadowVoiceStudio;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class PostShadowVoicePresetController extends AbstractController
{
    #[Route('/api/shadow/voice/preset', name: 'api_shadow_voice_preset', methods: ['POST'])]
    public function __invoke(Request $request, ShadowVoiceStudio $studio): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);

        if (!is_array($payload)) {
            return $this->json(['error' => 'Invalid JSON payload.'], 400);
        }

        $presetId = is_string($payload['preset'] ?? null) ? $payload['preset'] : 'custom';
        $preset = ShadowVoicePreset::tryFrom($presetId) ?? ShadowVoicePreset::Custom;

        return $this->json($studio->applyPreset($preset));
    }
}
