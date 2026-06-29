import {
	contentConversationChatPath,
	contentConversationChatStreamPath,
	conversationDocumentsPath,
} from "@/config/api";
import { parseSseEvents } from "@/services/chat/types";
import type { HttpClient } from "@/services/http/HttpClient";
import { ApiError, NetworkError } from "@/shared/errors";
import type { ConversationRepository } from "./ConversationRepository";
import {
	type Conversation,
	type ConversationChatApiDto,
	type ConversationChatResult,
	type ConversationStreamApiDto,
	type ConversationStreamCallbacks,
	EMPTY_CONVERSATION,
	EMPTY_CONVERSATION_CHAT_RESULT,
	mapConversationChatFromApi,
	mapConversationStreamFromApi,
	mapConversationStreamTokenFromApi,
	mapUpdateConversationDocumentsFromApi,
	type UpdateConversationDocumentsApiDto,
} from "./types";

/**
 * HttpClient only supports JSON request/response cycles. SSE streaming requires
 * reading a text/event-stream body, so fetch() is used here exclusively.
 */
export class HttpConversationRepository implements ConversationRepository {
	private readonly httpClient: HttpClient;
	private readonly baseUrl: string;

	constructor(httpClient: HttpClient, baseUrl: string) {
		this.httpClient = httpClient;
		this.baseUrl = baseUrl.replace(/\/$/, "");
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

	async streamQuestion(
		contentId: string,
		conversationId: string,
		question: string,
		callbacks: ConversationStreamCallbacks,
	): Promise<void> {
		const path = contentConversationChatStreamPath(contentId, conversationId);

		try {
			const response = await fetch(`${this.baseUrl}${path}`, {
				method: "POST",
				headers: {
					Accept: "text/event-stream",
					"Content-Type": "application/json",
				},
				body: JSON.stringify({ question }),
			});

			if (!response.ok) {
				callbacks.onError(
					new ApiError(
						`POST ${path} failed (${response.status})`,
						response.status,
					),
				);
				return;
			}

			this.dispatchSseEvents(await response.text(), callbacks);
		} catch (error) {
			if (error instanceof ApiError) {
				callbacks.onError(error);
				return;
			}

			callbacks.onError(
				error instanceof Error
					? error
					: new NetworkError(`POST ${path} failed`, error),
			);
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

	private dispatchSseEvents(
		content: string,
		callbacks: ConversationStreamCallbacks,
	): void {
		const events = parseSseEvents(content);
		let sawConversation = false;
		let sawDone = false;

		for (const { event, data } of events) {
			if (event === "done") {
				sawDone = true;
				callbacks.onDone();
				return;
			}

			if (event === "conversation") {
				let parsed: unknown;

				try {
					parsed = JSON.parse(data);
				} catch {
					callbacks.onError(new Error("Malformed SSE conversation payload"));
					return;
				}

				const conversation = mapConversationStreamFromApi(
					parsed as ConversationStreamApiDto,
				);

				if (conversation === null) {
					callbacks.onError(new Error("Malformed SSE conversation payload"));
					return;
				}

				sawConversation = true;
				callbacks.onConversation(conversation);
				continue;
			}

			if (event !== "token") {
				callbacks.onError(new Error(`Unexpected SSE event: ${event}`));
				return;
			}

			let parsed: unknown;

			try {
				parsed = JSON.parse(data);
			} catch {
				callbacks.onError(new Error("Malformed SSE token payload"));
				return;
			}

			const token = mapConversationStreamTokenFromApi(
				parsed as { index: number; text: string },
			);

			if (token === null) {
				callbacks.onError(new Error("Malformed SSE token payload"));
				return;
			}

			callbacks.onToken(token);
		}

		if (!sawDone) {
			if (!sawConversation) {
				callbacks.onError(
					new Error("SSE stream ended without conversation event"),
				);
				return;
			}

			callbacks.onError(new Error("SSE stream ended without done event"));
		}
	}
}
