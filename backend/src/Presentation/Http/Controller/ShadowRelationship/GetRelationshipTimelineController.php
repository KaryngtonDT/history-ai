<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\ShadowRelationship;

use App\Application\ShadowRelationship\Handlers\GetRelationshipTimelineHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class GetRelationshipTimelineController extends AbstractController
{
    #[Route('/api/shadow/relationship/timeline', name: 'api_shadow_relationship_timeline', methods: ['GET'])]
    public function __invoke(Request $request, GetRelationshipTimelineHandler $handler): JsonResponse
    {
        $scopeKey = is_string($request->query->get('scopeKey')) ? $request->query->get('scopeKey') : 'default';

        return $this->json($handler($scopeKey));
    }
}
