import { contentAgentRunPath } from "@/config/api";
import type { HttpClient } from "@/services/http/HttpClient";
import { ApiError } from "@/shared/errors";
import type { AgentRepository } from "./AgentRepository";
import {
	type AgentExecution,
	type AgentExecutionApiDto,
	EMPTY_AGENT_EXECUTION,
	mapAgentExecutionFromApi,
	type RunAgentRequestDto,
} from "./types";

export class HttpAgentRepository implements AgentRepository {
	private readonly httpClient: HttpClient;

	constructor(httpClient: HttpClient) {
		this.httpClient = httpClient;
	}

	async runAgent(
		contentId: string,
		question: string,
		conversationId?: string,
	): Promise<AgentExecution> {
		const payload: RunAgentRequestDto = { question };

		if (conversationId !== undefined && conversationId.trim() !== "") {
			payload.conversationId = conversationId;
		}

		try {
			const dto = await this.httpClient.post<AgentExecutionApiDto>(
				contentAgentRunPath(contentId),
				payload,
			);

			return mapAgentExecutionFromApi(dto);
		} catch (error) {
			if (error instanceof ApiError && error.status === 400) {
				return EMPTY_AGENT_EXECUTION;
			}

			throw error;
		}
	}
}
