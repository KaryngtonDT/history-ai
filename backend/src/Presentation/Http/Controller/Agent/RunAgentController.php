<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Agent;

use App\Application\Agent\Commands\RunAgentCommand;
use App\Application\Agent\Handlers\RunAgentHandler;
use App\Domain\Agent\Exception\InvalidAgentPlanException;
use App\Domain\Chat\Exception\InvalidConversationIdException;
use App\Domain\Content\ContentId;
use App\Domain\Content\Exception\InvalidContentIdException;
use App\Presentation\Http\Request\Agent\Exception\InvalidAgentRequestException;
use App\Presentation\Http\Request\Agent\RunAgentRequest;
use App\Presentation\Http\Response\Agent\AgentExecutionResponse;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class RunAgentController extends AbstractController
{
    #[OA\Post(
        operationId: 'runContentAgent',
        summary: 'Run deterministic agent workflow',
        description: 'Plans and executes a deterministic agent workflow for a content resource, returning the plan and execution trace without calling real tools.',
        tags: ['Agent'],
        parameters: [
            new OA\Parameter(
                name: 'contentId',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'uuid'),
                example: '550e8400-e29b-41d4-a716-446655440000',
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/AgentRunRequest'),
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Agent plan and execution trace',
                content: new OA\JsonContent(ref: '#/components/schemas/AgentExecution'),
            ),
            new OA\Response(
                response: 400,
                description: 'Invalid request',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse'),
            ),
        ],
    )]
    #[Route(
        '/api/contents/{contentId}/agent/run',
        name: 'api_contents_agent_run',
        methods: ['POST'],
    )]
    public function __invoke(
        string $contentId,
        Request $request,
        RunAgentHandler $handler,
    ): JsonResponse {
        try {
            new ContentId($contentId);
        } catch (InvalidContentIdException) {
            return $this->invalidRequestResponse();
        }

        $payload = json_decode($request->getContent(), true);

        if (!is_array($payload)) {
            return $this->invalidRequestResponse();
        }

        try {
            $agentRequest = RunAgentRequest::fromArray($payload);
        } catch (InvalidAgentRequestException) {
            return $this->invalidRequestResponse();
        }

        try {
            $result = $handler(new RunAgentCommand(
                contentId: $contentId,
                question: $agentRequest->question,
                conversationId: $agentRequest->conversationId,
            ));
        } catch (InvalidAgentPlanException|InvalidConversationIdException) {
            return $this->invalidRequestResponse();
        }

        return $this->json(AgentExecutionResponse::fromResult($result));
    }

    private function invalidRequestResponse(): JsonResponse
    {
        return $this->json(['error' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
    }
}
