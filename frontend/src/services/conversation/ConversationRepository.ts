import type { ConversationChatResult } from "./types";

export interface ConversationRepository {
	askQuestion(
		contentId: string,
		conversationId: string,
		question: string,
	): Promise<ConversationChatResult>;
}
