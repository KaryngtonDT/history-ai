<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Review;

use App\Application\Review\BuildPreferenceProfileHandler;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GetPreferencesController extends AbstractController
{
    #[OA\Get(
        operationId: 'getPreferences',
        summary: 'Get the current user preference profile',
        tags: ['Review'],
        responses: [
            new OA\Response(response: 200, description: 'Preference profile', content: new OA\JsonContent(ref: '#/components/schemas/PreferenceProfile')),
            new OA\Response(response: 404, description: 'Profile not found', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
        ],
    )]
    #[Route('/api/preferences', name: 'api_preferences_get', methods: ['GET'])]
    public function __invoke(BuildPreferenceProfileHandler $handler): JsonResponse
    {
        $profile = $handler->getCurrent();

        if (null === $profile) {
            return $this->json(['error' => 'Preference profile not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json([
            'translationStyle' => $profile->translationStyle,
            'voiceStability' => $profile->voiceStability,
            'renderingPreset' => $profile->renderingPreset,
            'lipSyncStrength' => $profile->lipSyncStrength,
            'latestComment' => $profile->latestComment,
            'reviewCount' => $profile->reviewCount,
            'explanationLines' => $profile->explanationLines,
        ]);
    }
}
