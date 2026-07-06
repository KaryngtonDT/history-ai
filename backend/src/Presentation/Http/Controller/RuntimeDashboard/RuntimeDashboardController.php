<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\RuntimeDashboard;

use App\Application\RuntimeDashboard\RuntimeDashboardInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/runtime')]
final class RuntimeDashboardController extends AbstractController
{
    public function __construct(private readonly RuntimeDashboardInterface $dashboard)
    {
    }

    #[OA\Get(operationId: 'getRuntimeDashboard', tags: ['Runtime'])]
    #[Route('/dashboard', name: 'api_runtime_dashboard', methods: ['GET'])]
    public function dashboard(): JsonResponse
    {
        return $this->json($this->dashboard->dashboard());
    }
}
