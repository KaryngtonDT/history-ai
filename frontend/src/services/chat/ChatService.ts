import type { ChatRepository } from "./ChatRepository";
import { createChatRepository } from "./ChatRepositoryFactory";
import {
	type ChatAnswer,
	type ChatStreamCallbacks,
	EMPTY_CHAT_ANSWER,
} from "./types";

const CONTENT_ID_PATTERN =
	/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i;

export class ChatService {
	private readonly repository: ChatRepository;

	constructor(repository: ChatRepository) {
		this.repository = repository;
	}

	askQuestion(contentId: string, question: string): Promise<ChatAnswer> {
		const normalizedContentId = contentId.trim();
		const normalizedQuestion = question.trim();

		if (
			normalizedContentId === "" ||
			normalizedQuestion === "" ||
			!CONTENT_ID_PATTERN.test(normalizedContentId)
		) {
			return Promise.resolve(EMPTY_CHAT_ANSWER);
		}

		return this.repository.askQuestion(normalizedContentId, normalizedQuestion);
	}

	streamQuestion(
		contentId: string,
		question: string,
		callbacks: ChatStreamCallbacks,
	): Promise<void> {
		const normalizedContentId = contentId.trim();
		const normalizedQuestion = question.trim();

		if (
			normalizedContentId === "" ||
			!CONTENT_ID_PATTERN.test(normalizedContentId)
		) {
			callbacks.onError(new Error("Invalid content id"));
			return Promise.resolve();
		}

		if (normalizedQuestion === "") {
			callbacks.onError(new Error("Invalid question"));
			return Promise.resolve();
		}

		return this.repository.streamQuestion(
			normalizedContentId,
			normalizedQuestion,
			callbacks,
		);
	}
}

export const chatService = new ChatService(createChatRepository());
