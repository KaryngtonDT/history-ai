import type { AgentRepository } from "./AgentRepository";
import { createAgentRepository } from "./AgentRepositoryFactory";
import { type AgentExecution, EMPTY_AGENT_EXECUTION } from "./types";

const UUID_PATTERN =
	/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i;

const MAX_QUESTION_LENGTH = 2000;

export class AgentService {
	private readonly repository: AgentRepository;

	constructor(repository: AgentRepository) {
		this.repository = repository;
	}

	runAgent(
		contentId: string,
		question: string,
		conversationId?: string,
	): Promise<AgentExecution> {
		const normalizedContentId = contentId.trim();
		const trimmedQuestion = question.trim();
		const normalizedConversationId = conversationId?.trim() ?? "";

		if (
			normalizedContentId === "" ||
			!UUID_PATTERN.test(normalizedContentId) ||
			trimmedQuestion === "" ||
			trimmedQuestion.length > MAX_QUESTION_LENGTH ||
			(normalizedConversationId !== "" &&
				!UUID_PATTERN.test(normalizedConversationId))
		) {
			return Promise.resolve(EMPTY_AGENT_EXECUTION);
		}

		return this.repository.runAgent(
			normalizedContentId,
			trimmedQuestion,
			normalizedConversationId === "" ? undefined : normalizedConversationId,
		);
	}
}

export const agentService = new AgentService(createAgentRepository());
