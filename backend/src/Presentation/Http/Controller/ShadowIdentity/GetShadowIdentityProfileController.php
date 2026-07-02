<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\ShadowIdentity;

use App\Application\ShadowIdentity\Handlers\GetShadowIdentityProfileHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class GetShadowIdentityProfileController extends AbstractController
{
    #[Route('/api/shadow/identity/profile', name: 'api_shadow_identity_profile', methods: ['GET'])]
    public function __invoke(Request $request, GetShadowIdentityProfileHandler $handler): JsonResponse
    {
        $scopeKey = is_string($request->query->get('scopeKey')) ? $request->query->get('scopeKey') : 'default';

        return $this->json($handler($scopeKey));
    }
}
