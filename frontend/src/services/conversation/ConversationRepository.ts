import type {
	Conversation,
	ConversationChatResult,
	ConversationStreamCallbacks,
} from "./types";

export interface ConversationRepository {
	askQuestion(
		contentId: string,
		conversationId: string,
		question: string,
	): Promise<ConversationChatResult>;

	streamQuestion(
		contentId: string,
		conversationId: string,
		question: string,
		callbacks: ConversationStreamCallbacks,
	): Promise<void>;

	updateDocuments(
		conversationId: string,
		contentIds: string[],
	): Promise<Conversation>;
}
