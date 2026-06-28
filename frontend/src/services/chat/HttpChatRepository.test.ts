import { describe, expect, it, vi } from "vitest";
import type { HttpClient } from "@/services/http/HttpClient";
import { ApiError } from "@/shared/errors";
import { HttpChatRepository } from "./HttpChatRepository";

describe("HttpChatRepository", () => {
	it("uses POST /api/contents/{contentId}/chat with question body", async () => {
		const post = vi.fn().mockResolvedValue({
			answer: "Mock answer based on retrieved context.",
			sources: [],
			citations: [],
		});
		const httpClient = { get: vi.fn(), post } as unknown as HttpClient;
		const repository = new HttpChatRepository(httpClient);

		await repository.askQuestion(
			"550e8400-e29b-41d4-a716-446655440000",
			"Why did Rome fall?",
		);

		expect(post).toHaveBeenCalledWith(
			"/api/contents/550e8400-e29b-41d4-a716-446655440000/chat",
			{ question: "Why did Rome fall?" },
		);
	});

	it("maps API DTO to chat answer with citations", async () => {
		const post = vi.fn().mockResolvedValue({
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
		});
		const httpClient = { get: vi.fn(), post } as unknown as HttpClient;
		const repository = new HttpChatRepository(httpClient);

		const result = await repository.askQuestion(
			"550e8400-e29b-41d4-a716-446655440000",
			"Why did Rome fall?",
		);

		expect(result).toEqual({
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
		});
	});

	it("returns empty answer when request is invalid on the server", async () => {
		const post = vi.fn().mockRejectedValue(new ApiError("POST failed", 400));
		const httpClient = { get: vi.fn(), post } as unknown as HttpClient;
		const repository = new HttpChatRepository(httpClient);

		const result = await repository.askQuestion(
			"550e8400-e29b-41d4-a716-446655440000",
			"",
		);

		expect(result).toEqual({ answer: "", sources: [], citations: [] });
	});

	it("propagates non-400 HTTP errors", async () => {
		const post = vi.fn().mockRejectedValue(new ApiError("POST failed", 500));
		const httpClient = { get: vi.fn(), post } as unknown as HttpClient;
		const repository = new HttpChatRepository(httpClient);

		await expect(
			repository.askQuestion(
				"550e8400-e29b-41d4-a716-446655440000",
				"Why did Rome fall?",
			),
		).rejects.toBeInstanceOf(ApiError);
	});
});
