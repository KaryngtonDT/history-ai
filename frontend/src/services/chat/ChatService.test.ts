import { describe, expect, it, vi } from "vitest";
import type { ChatRepository } from "./ChatRepository";
import { ChatService } from "./ChatService";
import { EMPTY_CHAT_ANSWER } from "./types";

const answer = {
	answer: "Mock answer based on retrieved context.",
	sources: [
		{
			artifactId: "550e8400-e29b-41d4-a716-446655440002",
			chunkId: "550e8400-e29b-41d4-a716-446655440010",
			text: "## Ancient Rome",
			score: 0.87,
		},
	],
};

function createRepositoryMock(
	overrides: Partial<ChatRepository> = {},
): ChatRepository {
	return {
		askQuestion: vi.fn().mockResolvedValue(EMPTY_CHAT_ANSWER),
		...overrides,
	};
}

describe("ChatService", () => {
	it("returns chat answer from repository", async () => {
		const askQuestion = vi.fn().mockResolvedValue(answer);
		const service = new ChatService(createRepositoryMock({ askQuestion }));

		const response = await service.askQuestion(
			"550e8400-e29b-41d4-a716-446655440000",
			"Why did Rome fall?",
		);

		expect(askQuestion).toHaveBeenCalledWith(
			"550e8400-e29b-41d4-a716-446655440000",
			"Why did Rome fall?",
		);
		expect(response).toEqual(answer);
	});

	it("returns empty answer for empty content id without calling repository", async () => {
		const askQuestion = vi.fn();
		const service = new ChatService(createRepositoryMock({ askQuestion }));

		const response = await service.askQuestion("", "Why did Rome fall?");

		expect(askQuestion).not.toHaveBeenCalled();
		expect(response).toEqual(EMPTY_CHAT_ANSWER);
	});

	it("returns empty answer for empty question without calling repository", async () => {
		const askQuestion = vi.fn();
		const service = new ChatService(createRepositoryMock({ askQuestion }));

		const response = await service.askQuestion(
			"550e8400-e29b-41d4-a716-446655440000",
			"",
		);

		expect(askQuestion).not.toHaveBeenCalled();
		expect(response).toEqual(EMPTY_CHAT_ANSWER);
	});

	it("returns empty answer for invalid content id without calling repository", async () => {
		const askQuestion = vi.fn();
		const service = new ChatService(createRepositoryMock({ askQuestion }));

		const response = await service.askQuestion(
			"content-1",
			"Why did Rome fall?",
		);

		expect(askQuestion).not.toHaveBeenCalled();
		expect(response).toEqual(EMPTY_CHAT_ANSWER);
	});

	it("trims content id and question before delegating to repository", async () => {
		const askQuestion = vi.fn().mockResolvedValue(EMPTY_CHAT_ANSWER);
		const service = new ChatService(createRepositoryMock({ askQuestion }));

		await service.askQuestion(
			"  550e8400-e29b-41d4-a716-446655440000  ",
			"  Why did Rome fall?  ",
		);

		expect(askQuestion).toHaveBeenCalledWith(
			"550e8400-e29b-41d4-a716-446655440000",
			"Why did Rome fall?",
		);
	});
});
