import { contentChatPath } from "@/config/api";
import type { HttpClient } from "@/services/http/HttpClient";
import { ApiError } from "@/shared/errors";
import type { ChatRepository } from "./ChatRepository";
import {
	type ChatAnswer,
	type ChatAnswerApiDto,
	type ChatRequestDto,
	EMPTY_CHAT_ANSWER,
	mapChatAnswerFromApi,
} from "./types";

export class HttpChatRepository implements ChatRepository {
	private readonly httpClient: HttpClient;

	constructor(httpClient: HttpClient) {
		this.httpClient = httpClient;
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
}
