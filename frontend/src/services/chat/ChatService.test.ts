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
	citations: [
		{
			number: 1,
			artifactId: "550e8400-e29b-41d4-a716-446655440002",
			chunkId: "550e8400-e29b-41d4-a716-446655440010",
			score: 0.87,
		},
	],
};

function createRepositoryMock(
	overrides: Partial<ChatRepository> = {},
): ChatRepository {
	return {
		askQuestion: vi.fn().mockResolvedValue(EMPTY_CHAT_ANSWER),
		streamQuestion: vi.fn().mockResolvedValue(undefined),
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

	it("delegates streamQuestion to repository after validation", async () => {
		const streamQuestion = vi.fn().mockResolvedValue(undefined);
		const service = new ChatService(createRepositoryMock({ streamQuestion }));
		const callbacks = {
			onToken: vi.fn(),
			onDone: vi.fn(),
			onError: vi.fn(),
		};

		await service.streamQuestion(
			"550e8400-e29b-41d4-a716-446655440000",
			"Why did Rome fall?",
			callbacks,
		);

		expect(streamQuestion).toHaveBeenCalledWith(
			"550e8400-e29b-41d4-a716-446655440000",
			"Why did Rome fall?",
			callbacks,
		);
	});

	it("calls onError for invalid content id without calling repository", async () => {
		const streamQuestion = vi.fn();
		const service = new ChatService(createRepositoryMock({ streamQuestion }));
		const onError = vi.fn();

		await service.streamQuestion("content-1", "Why did Rome fall?", {
			onToken: vi.fn(),
			onDone: vi.fn(),
			onError,
		});

		expect(streamQuestion).not.toHaveBeenCalled();
		expect(onError).toHaveBeenCalledWith(
			expect.objectContaining({
				message: "Invalid content id",
			}),
		);
	});

	it("calls onError for empty question without calling repository", async () => {
		const streamQuestion = vi.fn();
		const service = new ChatService(createRepositoryMock({ streamQuestion }));
		const onError = vi.fn();

		await service.streamQuestion("550e8400-e29b-41d4-a716-446655440000", "", {
			onToken: vi.fn(),
			onDone: vi.fn(),
			onError,
		});

		expect(streamQuestion).not.toHaveBeenCalled();
		expect(onError).toHaveBeenCalledWith(
			expect.objectContaining({
				message: "Invalid question",
			}),
		);
	});

	it("trims content id and question before delegating streamQuestion", async () => {
		const streamQuestion = vi.fn().mockResolvedValue(undefined);
		const service = new ChatService(createRepositoryMock({ streamQuestion }));
		const callbacks = {
			onToken: vi.fn(),
			onDone: vi.fn(),
			onError: vi.fn(),
		};

		await service.streamQuestion(
			"  550e8400-e29b-41d4-a716-446655440000  ",
			"  Why did Rome fall?  ",
			callbacks,
		);

		expect(streamQuestion).toHaveBeenCalledWith(
			"550e8400-e29b-41d4-a716-446655440000",
			"Why did Rome fall?",
			callbacks,
		);
	});
});
