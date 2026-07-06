<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\RuntimeCompletion;

use App\Application\RuntimeCompletion\RuntimeCompletionInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/runtime/completion')]
final class RuntimeCompletionController extends AbstractController
{
    public function __construct(private readonly RuntimeCompletionInterface $completion)
    {
    }

    #[OA\Get(operationId: 'getRuntimeCompletionPlan', tags: ['Runtime'])]
    #[Route('/plan', name: 'api_runtime_completion_plan', methods: ['GET'])]
    public function plan(): JsonResponse
    {
        return $this->json($this->completion->plan());
    }

    #[OA\Post(operationId: 'executeRuntimeCompletion', tags: ['Runtime'])]
    #[Route('/execute', name: 'api_runtime_completion_execute', methods: ['POST'])]
    public function execute(): JsonResponse
    {
        return $this->json($this->completion->execute());
    }
}
