import { artifactMocksByContentId } from "@/mock/artifact";
import type { ChatRepository } from "./ChatRepository";
import {
	buildMockAnswerWithCitationMarkers,
	buildMockChatCitationsFromSources,
	buildMockChatSourcesFromArtifacts,
	type ChatAnswer,
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
}
