import { artifactMocksByContentId } from "@/mock/artifact";
import type { ChatRepository } from "./ChatRepository";
import {
	buildMockChatSourcesFromArtifacts,
	type ChatAnswer,
	MOCK_CHAT_ANSWER,
} from "./types";

export class MockChatRepository implements ChatRepository {
	async askQuestion(contentId: string, question: string): Promise<ChatAnswer> {
		const artifacts = artifactMocksByContentId[contentId];

		if (artifacts === undefined || artifacts.length === 0) {
			return {
				answer: MOCK_CHAT_ANSWER,
				sources: [],
			};
		}

		return {
			answer: MOCK_CHAT_ANSWER,
			sources: buildMockChatSourcesFromArtifacts(artifacts, question),
		};
	}
}
