<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\ShadowVoice;

use App\Application\ShadowVoice\ShadowVoiceStudio;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class GetShadowVoiceLibraryController extends AbstractController
{
    #[Route('/api/shadow/voice/library', name: 'api_shadow_voice_library', methods: ['GET'])]
    public function __invoke(ShadowVoiceStudio $studio): JsonResponse
    {
        return $this->json($studio->library());
    }
}
