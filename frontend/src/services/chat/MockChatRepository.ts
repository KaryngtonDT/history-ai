import { artifactMocksByContentId } from "@/mock/artifact";
import type { ChatRepository } from "./ChatRepository";
import {
	buildMockAnswerWithCitationMarkers,
	buildMockChatCitationsFromSources,
	buildMockChatSourcesFromArtifacts,
	buildMockStreamTokens,
	type ChatAnswer,
	type ChatStreamCallbacks,
} from "./types";

export class MockChatRepository implements ChatRepository {
	async askQuestion(contentId: string, question: string): Promise<ChatAnswer> {
		const artifacts = artifactMocksByContentId[contentId];

		if (artifacts === undefined || artifacts.length === 0) {
			return {
				answer: buildMockAnswerWithCitationMarkers(0),
				sources: [],
				citations: [],
			};
		}

		const sources = buildMockChatSourcesFromArtifacts(artifacts, question);

		return {
			answer: buildMockAnswerWithCitationMarkers(sources.length),
			sources,
			citations: buildMockChatCitationsFromSources(sources),
		};
	}

	async streamQuestion(
		contentId: string,
		question: string,
		callbacks: ChatStreamCallbacks,
	): Promise<void> {
		const answer = await this.askQuestion(contentId, question);
		const tokens = buildMockStreamTokens(answer.sources.length);

		for (const [index, text] of tokens.entries()) {
			callbacks.onToken({ index, text });
		}

		callbacks.onDone();
	}
}
