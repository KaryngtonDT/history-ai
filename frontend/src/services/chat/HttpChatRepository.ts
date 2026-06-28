import { contentChatPath, contentChatStreamPath } from "@/config/api";
import type { HttpClient } from "@/services/http/HttpClient";
import { ApiError, NetworkError } from "@/shared/errors";
import type { ChatRepository } from "./ChatRepository";
import {
	type ChatAnswer,
	type ChatAnswerApiDto,
	type ChatRequestDto,
	type ChatStreamCallbacks,
	EMPTY_CHAT_ANSWER,
	mapChatAnswerFromApi,
	mapChatStreamTokenFromApi,
	parseSseEvents,
} from "./types";

/**
 * HttpClient only supports JSON request/response cycles. SSE streaming requires
 * reading a text/event-stream body, so fetch() is used here exclusively.
 */
export class HttpChatRepository implements ChatRepository {
	private readonly httpClient: HttpClient;
	private readonly baseUrl: string;

	constructor(httpClient: HttpClient, baseUrl: string) {
		this.httpClient = httpClient;
		this.baseUrl = baseUrl.replace(/\/$/, "");
	}

	async askQuestion(contentId: string, question: string): Promise<ChatAnswer> {
		try {
			const dto = await this.httpClient.post<ChatAnswerApiDto>(
				contentChatPath(contentId),
				{ question } satisfies ChatRequestDto,
			);

			return mapChatAnswerFromApi(dto);
		} catch (error) {
			if (error instanceof ApiError && error.status === 400) {
				return EMPTY_CHAT_ANSWER;
			}

			throw error;
		}
	}

	async streamQuestion(
		contentId: string,
		question: string,
		callbacks: ChatStreamCallbacks,
	): Promise<void> {
		const path = contentChatStreamPath(contentId);

		try {
			const response = await fetch(`${this.baseUrl}${path}`, {
				method: "POST",
				headers: {
					Accept: "text/event-stream",
					"Content-Type": "application/json",
				},
				body: JSON.stringify({ question } satisfies ChatRequestDto),
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

	private dispatchSseEvents(
		content: string,
		callbacks: ChatStreamCallbacks,
	): void {
		const events = parseSseEvents(content);
		let sawDone = false;

		for (const { event, data } of events) {
			if (event === "done") {
				sawDone = true;
				callbacks.onDone();
				return;
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

			const token = mapChatStreamTokenFromApi(
				parsed as { index: number; text: string },
			);

			if (token === null) {
				callbacks.onError(new Error("Malformed SSE token payload"));
				return;
			}

			callbacks.onToken(token);
		}

		if (!sawDone) {
			callbacks.onError(new Error("SSE stream ended without done event"));
		}
	}
}
