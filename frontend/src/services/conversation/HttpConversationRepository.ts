import { contentConversationChatPath } from "@/config/api";
import type { HttpClient } from "@/services/http/HttpClient";
import { ApiError } from "@/shared/errors";
import type { ConversationRepository } from "./ConversationRepository";
import {
	type ConversationChatApiDto,
	type ConversationChatResult,
	EMPTY_CONVERSATION_CHAT_RESULT,
	mapConversationChatFromApi,
} from "./types";

export class HttpConversationRepository implements ConversationRepository {
	private readonly httpClient: HttpClient;

	constructor(httpClient: HttpClient) {
		this.httpClient = httpClient;
	}

	async askQuestion(
		contentId: string,
		conversationId: string,
		question: string,
	): Promise<ConversationChatResult> {
		try {
			const dto = await this.httpClient.post<ConversationChatApiDto>(
				contentConversationChatPath(contentId, conversationId),
				{ question },
			);
			const result = mapConversationChatFromApi(dto);

			return result ?? EMPTY_CONVERSATION_CHAT_RESULT;
		} catch (error) {
			if (error instanceof ApiError && error.status === 400) {
				return EMPTY_CONVERSATION_CHAT_RESULT;
			}

			throw error;
		}
	}
}
