import type { AgentRepository } from "./AgentRepository";
import { type AgentExecution, buildMockAgentExecution } from "./types";

export class MockAgentRepository implements AgentRepository {
	async runAgent(
		_contentId: string,
		question: string,
		_conversationId?: string,
	): Promise<AgentExecution> {
		return buildMockAgentExecution(question);
	}
}
