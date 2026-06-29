import type { AgentExecution } from "./types";

export interface AgentRepository {
	runAgent(
		contentId: string,
		question: string,
		conversationId?: string,
	): Promise<AgentExecution>;
}
