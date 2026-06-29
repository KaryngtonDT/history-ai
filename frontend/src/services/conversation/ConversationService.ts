import type { ConversationRepository } from "./ConversationRepository";
import { createConversationRepository } from "./ConversationRepositoryFactory";
import {
	type Conversation,
	type ConversationChatResult,
	EMPTY_CONVERSATION,
	EMPTY_CONVERSATION_CHAT_RESULT,
	isValidContentId,
	isValidConversationId,
} from "./types";

export class ConversationService {
	private readonly repository: ConversationRepository;

	constructor(repository: ConversationRepository) {
		this.repository = repository;
	}

	askQuestion(
		contentId: string,
		conversationId: string,
		question: string,
	): Promise<ConversationChatResult> {
		const normalizedContentId = contentId.trim();
		const normalizedConversationId = conversationId.trim();
		const normalizedQuestion = question.trim();

		if (
			normalizedContentId === "" ||
			normalizedConversationId === "" ||
			normalizedQuestion === "" ||
			!isValidContentId(normalizedContentId) ||
			!isValidConversationId(normalizedConversationId)
		) {
			return Promise.resolve(EMPTY_CONVERSATION_CHAT_RESULT);
		}

		return this.repository.askQuestion(
			normalizedContentId,
			normalizedConversationId,
			normalizedQuestion,
		);
	}

	updateDocuments(
		conversationId: string,
		contentIds: string[],
	): Promise<Conversation> {
		const normalizedConversationId = conversationId.trim();
		const normalizedContentIds = contentIds
			.map((contentId) => contentId.trim())
			.filter((contentId) => contentId !== "");

		if (
			normalizedConversationId === "" ||
			normalizedContentIds.length === 0 ||
			!isValidConversationId(normalizedConversationId) ||
			!normalizedContentIds.every(isValidContentId)
		) {
			return Promise.resolve(EMPTY_CONVERSATION);
		}

		return this.repository.updateDocuments(
			normalizedConversationId,
			normalizedContentIds,
		);
	}
}

export const conversationService = new ConversationService(
	createConversationRepository(),
);
