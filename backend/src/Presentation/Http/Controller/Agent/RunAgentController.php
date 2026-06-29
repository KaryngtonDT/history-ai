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
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class RunAgentController extends AbstractController
{
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
