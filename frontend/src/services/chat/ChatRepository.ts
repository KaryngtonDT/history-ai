import type { ChatAnswer, ChatStreamCallbacks } from "./types";

export interface ChatRepository {
	askQuestion(contentId: string, question: string): Promise<ChatAnswer>;
	streamQuestion(
		contentId: string,
		question: string,
		callbacks: ChatStreamCallbacks,
	): Promise<void>;
}
