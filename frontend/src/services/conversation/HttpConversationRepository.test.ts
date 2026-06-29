import { describe, expect, it, vi } from "vitest";
import type { HttpClient } from "@/services/http/HttpClient";
import { ApiError } from "@/shared/errors";
import { HttpConversationRepository } from "./HttpConversationRepository";
import { EMPTY_CONVERSATION_CHAT_RESULT } from "./types";

describe("HttpConversationRepository", () => {
	it("uses POST /api/contents/{contentId}/conversations/{conversationId}/chat", async () => {
		const post = vi.fn().mockResolvedValue({
			conversation: {
				id: "550e8400-e29b-41d4-a716-446655440001",
				contentId: "550e8400-e29b-41d4-a716-446655440000",
				messages: [
					{ role: "user", text: "Why did Rome fall?" },
					{
						role: "assistant",
						text: "Mock answer based on retrieved context.",
					},
				],
			},
			answer: {
				answer: "Mock answer based on retrieved context.",
				sources: [],
				citations: [],
			},
		});
		const httpClient = { get: vi.fn(), post } as unknown as HttpClient;
		const repository = new HttpConversationRepository(httpClient);

		await repository.askQuestion(
			"550e8400-e29b-41d4-a716-446655440000",
			"550e8400-e29b-41d4-a716-446655440001",
			"Why did Rome fall?",
		);

		expect(post).toHaveBeenCalledWith(
			"/api/contents/550e8400-e29b-41d4-a716-446655440000/conversations/550e8400-e29b-41d4-a716-446655440001/chat",
			{ question: "Why did Rome fall?" },
		);
	});

	it("maps API DTO to conversation chat result", async () => {
		const post = vi.fn().mockResolvedValue({
			conversation: {
				id: "550e8400-e29b-41d4-a716-446655440001",
				contentId: "550e8400-e29b-41d4-a716-446655440000",
				messages: [
					{ role: "user", text: "Why did Rome fall?" },
					{
						role: "assistant",
						text: "Mock answer based on retrieved context [1].",
					},
				],
			},
			answer: {
				answer: "Mock answer based on retrieved context [1].",
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
			},
		});
		const httpClient = { get: vi.fn(), post } as unknown as HttpClient;
		const repository = new HttpConversationRepository(httpClient);

		const response = await repository.askQuestion(
			"550e8400-e29b-41d4-a716-446655440000",
			"550e8400-e29b-41d4-a716-446655440001",
			"Why did Rome fall?",
		);

		expect(response.conversation.messages).toHaveLength(2);
		expect(response.answer.citations).toHaveLength(1);
	});

	it("returns empty result for HTTP 400", async () => {
		const post = vi
			.fn()
			.mockRejectedValue(new ApiError("Invalid request", 400));
		const httpClient = { get: vi.fn(), post } as unknown as HttpClient;
		const repository = new HttpConversationRepository(httpClient);

		const response = await repository.askQuestion(
			"550e8400-e29b-41d4-a716-446655440000",
			"550e8400-e29b-41d4-a716-446655440001",
			"Why did Rome fall?",
		);

		expect(response).toEqual(EMPTY_CONVERSATION_CHAT_RESULT);
	});

	it("returns empty result for malformed API payload", async () => {
		const post = vi.fn().mockResolvedValue({
			conversation: {
				id: "invalid",
				contentId: "550e8400-e29b-41d4-a716-446655440000",
				messages: [],
			},
			answer: {
				answer: "Mock answer.",
				sources: [],
			},
		});
		const httpClient = { get: vi.fn(), post } as unknown as HttpClient;
		const repository = new HttpConversationRepository(httpClient);

		const response = await repository.askQuestion(
			"550e8400-e29b-41d4-a716-446655440000",
			"550e8400-e29b-41d4-a716-446655440001",
			"Why did Rome fall?",
		);

		expect(response).toEqual(EMPTY_CONVERSATION_CHAT_RESULT);
	});
});
