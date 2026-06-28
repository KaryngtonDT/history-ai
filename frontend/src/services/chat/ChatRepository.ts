import type { ChatAnswer } from "./types";

export interface ChatRepository {
	askQuestion(contentId: string, question: string): Promise<ChatAnswer>;
}
