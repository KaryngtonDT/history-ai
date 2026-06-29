import type { Conversation, ConversationChatResult } from "./types";

export interface ConversationRepository {
	askQuestion(
		contentId: string,
		conversationId: string,
		question: string,
	): Promise<ConversationChatResult>;

	updateDocuments(
		conversationId: string,
		contentIds: string[],
	): Promise<Conversation>;
}
