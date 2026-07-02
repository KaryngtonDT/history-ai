<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\ShadowVoice;

use App\Application\ShadowVoice\ShadowVoiceStudio;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class GetShadowVoiceCollectionsController extends AbstractController
{
    #[Route('/api/shadow/voice/collections', name: 'api_shadow_voice_collections', methods: ['GET'])]
    public function __invoke(ShadowVoiceStudio $studio): JsonResponse
    {
        return $this->json([
            'collections' => $studio->collections(),
            'presets' => $studio->presets(),
        ]);
    }
}
