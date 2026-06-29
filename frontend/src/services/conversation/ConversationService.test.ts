import { describe, expect, it, vi } from "vitest";
import type { ConversationRepository } from "./ConversationRepository";
import { ConversationService } from "./ConversationService";
import { EMPTY_CONVERSATION, EMPTY_CONVERSATION_CHAT_RESULT } from "./types";

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
		documents: [{ contentId: "550e8400-e29b-41d4-a716-446655440000" }],
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
		streamQuestion: vi.fn().mockResolvedValue(undefined),
		updateDocuments: vi.fn().mockResolvedValue(EMPTY_CONVERSATION),
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

	it("returns updated conversation from repository", async () => {
		const updatedConversation = {
			id: "550e8400-e29b-41d4-a716-446655440001",
			contentId: "550e8400-e29b-41d4-a716-446655440099",
			messages: result.conversation.messages,
			documents: [
				{ contentId: "550e8400-e29b-41d4-a716-446655440099" },
				{ contentId: "550e8400-e29b-41d4-a716-446655440000" },
			],
		};
		const updateDocuments = vi.fn().mockResolvedValue(updatedConversation);
		const service = new ConversationService(
			createRepositoryMock({ updateDocuments }),
		);

		const response = await service.updateDocuments(
			"550e8400-e29b-41d4-a716-446655440001",
			[
				"550e8400-e29b-41d4-a716-446655440099",
				"550e8400-e29b-41d4-a716-446655440000",
			],
		);

		expect(updateDocuments).toHaveBeenCalledWith(
			"550e8400-e29b-41d4-a716-446655440001",
			[
				"550e8400-e29b-41d4-a716-446655440099",
				"550e8400-e29b-41d4-a716-446655440000",
			],
		);
		expect(response).toEqual(updatedConversation);
	});

	it("returns empty conversation for invalid conversation id without calling repository", async () => {
		const updateDocuments = vi.fn();
		const service = new ConversationService(
			createRepositoryMock({ updateDocuments }),
		);

		const response = await service.updateDocuments("invalid", [
			"550e8400-e29b-41d4-a716-446655440000",
		]);

		expect(updateDocuments).not.toHaveBeenCalled();
		expect(response).toEqual(EMPTY_CONVERSATION);
	});

	it("returns empty conversation for empty content ids without calling repository", async () => {
		const updateDocuments = vi.fn();
		const service = new ConversationService(
			createRepositoryMock({ updateDocuments }),
		);

		const response = await service.updateDocuments(
			"550e8400-e29b-41d4-a716-446655440001",
			[],
		);

		expect(updateDocuments).not.toHaveBeenCalled();
		expect(response).toEqual(EMPTY_CONVERSATION);
	});

	it("returns empty conversation for invalid content id without calling repository", async () => {
		const updateDocuments = vi.fn();
		const service = new ConversationService(
			createRepositoryMock({ updateDocuments }),
		);

		const response = await service.updateDocuments(
			"550e8400-e29b-41d4-a716-446655440001",
			["invalid"],
		);

		expect(updateDocuments).not.toHaveBeenCalled();
		expect(response).toEqual(EMPTY_CONVERSATION);
	});

	it("delegates streamQuestion to repository after validation", async () => {
		const streamQuestion = vi.fn().mockResolvedValue(undefined);
		const service = new ConversationService(
			createRepositoryMock({ streamQuestion }),
		);
		const callbacks = {
			onToken: vi.fn(),
			onConversation: vi.fn(),
			onDone: vi.fn(),
			onError: vi.fn(),
		};

		await service.streamQuestion(
			"550e8400-e29b-41d4-a716-446655440000",
			"550e8400-e29b-41d4-a716-446655440001",
			"Why did Rome fall?",
			callbacks,
		);

		expect(streamQuestion).toHaveBeenCalledWith(
			"550e8400-e29b-41d4-a716-446655440000",
			"550e8400-e29b-41d4-a716-446655440001",
			"Why did Rome fall?",
			callbacks,
		);
	});

	it("calls onError for invalid content id without calling repository", async () => {
		const streamQuestion = vi.fn();
		const onError = vi.fn();
		const service = new ConversationService(
			createRepositoryMock({ streamQuestion }),
		);

		await service.streamQuestion(
			"invalid",
			"550e8400-e29b-41d4-a716-446655440001",
			"Why did Rome fall?",
			{
				onToken: vi.fn(),
				onConversation: vi.fn(),
				onDone: vi.fn(),
				onError,
			},
		);

		expect(streamQuestion).not.toHaveBeenCalled();
		expect(onError).toHaveBeenCalledWith(expect.any(Error));
	});

	it("calls onError for invalid conversation id without calling repository", async () => {
		const streamQuestion = vi.fn();
		const onError = vi.fn();
		const service = new ConversationService(
			createRepositoryMock({ streamQuestion }),
		);

		await service.streamQuestion(
			"550e8400-e29b-41d4-a716-446655440000",
			"invalid",
			"Why did Rome fall?",
			{
				onToken: vi.fn(),
				onConversation: vi.fn(),
				onDone: vi.fn(),
				onError,
			},
		);

		expect(streamQuestion).not.toHaveBeenCalled();
		expect(onError).toHaveBeenCalledWith(expect.any(Error));
	});

	it("calls onError for empty question without calling repository", async () => {
		const streamQuestion = vi.fn();
		const onError = vi.fn();
		const service = new ConversationService(
			createRepositoryMock({ streamQuestion }),
		);

		await service.streamQuestion(
			"550e8400-e29b-41d4-a716-446655440000",
			"550e8400-e29b-41d4-a716-446655440001",
			"   ",
			{
				onToken: vi.fn(),
				onConversation: vi.fn(),
				onDone: vi.fn(),
				onError,
			},
		);

		expect(streamQuestion).not.toHaveBeenCalled();
		expect(onError).toHaveBeenCalledWith(expect.any(Error));
	});
});
