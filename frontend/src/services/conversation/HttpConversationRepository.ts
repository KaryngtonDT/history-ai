import {
	contentConversationChatPath,
	conversationDocumentsPath,
} from "@/config/api";
import type { HttpClient } from "@/services/http/HttpClient";
import { ApiError } from "@/shared/errors";
import type { ConversationRepository } from "./ConversationRepository";
import {
	type Conversation,
	type ConversationChatApiDto,
	type ConversationChatResult,
	EMPTY_CONVERSATION,
	EMPTY_CONVERSATION_CHAT_RESULT,
	mapConversationChatFromApi,
	mapUpdateConversationDocumentsFromApi,
	type UpdateConversationDocumentsApiDto,
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

	async updateDocuments(
		conversationId: string,
		contentIds: string[],
	): Promise<Conversation> {
		try {
			const dto = await this.httpClient.put<UpdateConversationDocumentsApiDto>(
				conversationDocumentsPath(conversationId),
				{ contentIds },
			);
			const conversation = mapUpdateConversationDocumentsFromApi(dto);

			return conversation ?? EMPTY_CONVERSATION;
		} catch (error) {
			if (
				error instanceof ApiError &&
				(error.status === 400 || error.status === 404)
			) {
				return EMPTY_CONVERSATION;
			}

			throw error;
		}
	}
}
