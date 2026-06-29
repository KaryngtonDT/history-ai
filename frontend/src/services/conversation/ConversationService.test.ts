import { describe, expect, it, vi } from "vitest";
import type { ConversationRepository } from "./ConversationRepository";
import { ConversationService } from "./ConversationService";
import { EMPTY_CONVERSATION_CHAT_RESULT } from "./types";

const result = {
	conversation: {
		id: "550e8400-e29b-41d4-a716-446655440001",
		contentId: "550e8400-e29b-41d4-a716-446655440000",
		messages: [
			{ role: "user" as const, text: "Why did Rome fall?" },
			{
				role: "assistant" as const,
				text: "Mock answer based on retrieved context.",
			},
		],
	},
	answer: {
		answer: "Mock answer based on retrieved context.",
		sources: [],
		citations: [],
	},
};

function createRepositoryMock(
	overrides: Partial<ConversationRepository> = {},
): ConversationRepository {
	return {
		askQuestion: vi.fn().mockResolvedValue(EMPTY_CONVERSATION_CHAT_RESULT),
		...overrides,
	};
}

describe("ConversationService", () => {
	it("returns conversation chat result from repository", async () => {
		const askQuestion = vi.fn().mockResolvedValue(result);
		const service = new ConversationService(
			createRepositoryMock({ askQuestion }),
		);

		const response = await service.askQuestion(
			"550e8400-e29b-41d4-a716-446655440000",
			"550e8400-e29b-41d4-a716-446655440001",
			"Why did Rome fall?",
		);

		expect(askQuestion).toHaveBeenCalledWith(
			"550e8400-e29b-41d4-a716-446655440000",
			"550e8400-e29b-41d4-a716-446655440001",
			"Why did Rome fall?",
		);
		expect(response).toEqual(result);
	});

	it("returns empty result for invalid content id without calling repository", async () => {
		const askQuestion = vi.fn();
		const service = new ConversationService(
			createRepositoryMock({ askQuestion }),
		);

		const response = await service.askQuestion(
			"invalid",
			"550e8400-e29b-41d4-a716-446655440001",
			"Why did Rome fall?",
		);

		expect(askQuestion).not.toHaveBeenCalled();
		expect(response).toEqual(EMPTY_CONVERSATION_CHAT_RESULT);
	});

	it("returns empty result for invalid conversation id without calling repository", async () => {
		const askQuestion = vi.fn();
		const service = new ConversationService(
			createRepositoryMock({ askQuestion }),
		);

		const response = await service.askQuestion(
			"550e8400-e29b-41d4-a716-446655440000",
			"invalid",
			"Why did Rome fall?",
		);

		expect(askQuestion).not.toHaveBeenCalled();
		expect(response).toEqual(EMPTY_CONVERSATION_CHAT_RESULT);
	});

	it("returns empty result for empty question without calling repository", async () => {
		const askQuestion = vi.fn();
		const service = new ConversationService(
			createRepositoryMock({ askQuestion }),
		);

		const response = await service.askQuestion(
			"550e8400-e29b-41d4-a716-446655440000",
			"550e8400-e29b-41d4-a716-446655440001",
			"   ",
		);

		expect(askQuestion).not.toHaveBeenCalled();
		expect(response).toEqual(EMPTY_CONVERSATION_CHAT_RESULT);
	});
});
