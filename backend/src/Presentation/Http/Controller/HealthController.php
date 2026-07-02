<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller;

use App\Application\Platform\PlatformHealthCheckerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HealthController extends AbstractController
{
    public function __construct(
        private readonly PlatformHealthCheckerInterface $healthChecker,
    ) {
    }

    #[Route('/health', name: 'health', methods: ['GET'])]
    public function health(): JsonResponse
    {
        return $this->json($this->healthChecker->liveness());
    }

    #[Route('/ready', name: 'ready', methods: ['GET'])]
    public function ready(): JsonResponse
    {
        $payload = $this->healthChecker->readiness();
        $status = 'ready' === ($payload['status'] ?? '') ? Response::HTTP_OK : Response::HTTP_SERVICE_UNAVAILABLE;

        return $this->json($payload, $status);
    }

    #[Route('/live', name: 'live', methods: ['GET'])]
    public function live(): JsonResponse
    {
        $payload = $this->healthChecker->live();
        $status = 'live' === ($payload['status'] ?? '') ? Response::HTTP_OK : Response::HTTP_SERVICE_UNAVAILABLE;

        return $this->json($payload, $status);
    }

    #[Route('/api/platform/readiness', name: 'platform_readiness', methods: ['GET'])]
    public function readinessScore(): JsonResponse
    {
        return $this->json($this->healthChecker->productionReadiness());
    }
}
