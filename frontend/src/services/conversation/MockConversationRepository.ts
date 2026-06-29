import { artifactMocksByContentId } from "@/mock/artifact";
import {
	buildMockAnswerWithCitationMarkers,
	buildMockChatCitationsFromSources,
	buildMockChatSourcesFromArtifacts,
	type ChatAnswer,
} from "@/services/chat/types";
import type { ConversationRepository } from "./ConversationRepository";
import type {
	Conversation,
	ConversationChatResult,
	ConversationMessage,
	SelectedDocument,
} from "./types";

export class MockConversationRepository implements ConversationRepository {
	private readonly conversations = new Map<string, Conversation>();

	async askQuestion(
		contentId: string,
		conversationId: string,
		question: string,
	): Promise<ConversationChatResult> {
		const existing = this.conversations.get(conversationId);
		const conversation: Conversation =
			existing === undefined
				? {
						id: conversationId,
						contentId,
						messages: [],
						documents: [{ contentId }],
					}
				: existing;

		const userMessage: ConversationMessage = {
			role: "user",
			text: question,
		};
		const answer = this.buildMockAnswer(contentId, question);
		const assistantMessage: ConversationMessage = {
			role: "assistant",
			text: answer.answer,
		};

		const updatedConversation: Conversation = {
			id: conversationId,
			contentId: conversation.documents[0]?.contentId ?? contentId,
			messages: [...conversation.messages, userMessage, assistantMessage],
			documents: conversation.documents,
		};

		this.conversations.set(conversationId, updatedConversation);

		return {
			conversation: updatedConversation,
			answer,
		};
	}

	async updateDocuments(
		conversationId: string,
		contentIds: string[],
	): Promise<Conversation> {
		const existing = this.conversations.get(conversationId);

		if (existing === undefined) {
			return {
				id: "",
				contentId: "",
				messages: [],
				documents: [],
			};
		}

		const documents: SelectedDocument[] = contentIds.map((id) => ({
			contentId: id,
		}));
		const updatedConversation: Conversation = {
			...existing,
			contentId: documents[0]?.contentId ?? existing.contentId,
			documents,
		};

		this.conversations.set(conversationId, updatedConversation);

		return updatedConversation;
	}

	private buildMockAnswer(contentId: string, question: string): ChatAnswer {
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
