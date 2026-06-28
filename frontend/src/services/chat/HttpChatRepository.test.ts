import { describe, expect, it, vi } from "vitest";
import type { HttpClient } from "@/services/http/HttpClient";
import { ApiError } from "@/shared/errors";
import { HttpChatRepository } from "./HttpChatRepository";

const BASE_URL = "http://localhost:8000";

function createRepository(httpClient: HttpClient): HttpChatRepository {
	return new HttpChatRepository(httpClient, BASE_URL);
}

describe("HttpChatRepository", () => {
	it("uses POST /api/contents/{contentId}/chat with question body", async () => {
		const post = vi.fn().mockResolvedValue({
			answer: "Mock answer based on retrieved context.",
			sources: [],
			citations: [],
		});
		const httpClient = { get: vi.fn(), post } as unknown as HttpClient;
		const repository = createRepository(httpClient);

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
		const repository = createRepository(httpClient);

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
		const repository = createRepository(httpClient);

		const result = await repository.askQuestion(
			"550e8400-e29b-41d4-a716-446655440000",
			"",
		);

		expect(result).toEqual({ answer: "", sources: [], citations: [] });
	});

	it("propagates non-400 HTTP errors", async () => {
		const post = vi.fn().mockRejectedValue(new ApiError("POST failed", 500));
		const httpClient = { get: vi.fn(), post } as unknown as HttpClient;
		const repository = createRepository(httpClient);

		await expect(
			repository.askQuestion(
				"550e8400-e29b-41d4-a716-446655440000",
				"Why did Rome fall?",
			),
		).rejects.toBeInstanceOf(ApiError);
	});

	it("uses fetch for POST /api/contents/{contentId}/chat/stream", async () => {
		const fetchMock = vi.spyOn(globalThis, "fetch").mockResolvedValue(
			new Response(
				'event: token\ndata: {"index":0,"text":"Mock "}\n\nevent: done\ndata: {}\n\n',
				{
					status: 200,
					headers: { "Content-Type": "text/event-stream" },
				},
			),
		);
		const httpClient = {
			get: vi.fn(),
			post: vi.fn(),
		} as unknown as HttpClient;
		const repository = createRepository(httpClient);
		const onToken = vi.fn();
		const onDone = vi.fn();
		const onError = vi.fn();

		await repository.streamQuestion(
			"550e8400-e29b-41d4-a716-446655440000",
			"Why did Rome fall?",
			{ onToken, onDone, onError },
		);

		expect(fetchMock).toHaveBeenCalledWith(
			"http://localhost:8000/api/contents/550e8400-e29b-41d4-a716-446655440000/chat/stream",
			expect.objectContaining({
				method: "POST",
				body: JSON.stringify({ question: "Why did Rome fall?" }),
			}),
		);
		expect(onToken).toHaveBeenCalledWith({ index: 0, text: "Mock " });
		expect(onDone).toHaveBeenCalledOnce();
		expect(onError).not.toHaveBeenCalled();

		fetchMock.mockRestore();
	});

	it("parses token events in order and emits done", async () => {
		const fetchMock = vi.spyOn(globalThis, "fetch").mockResolvedValue(
			new Response(
				[
					"event: token",
					'data: {"index":0,"text":"Mock "}',
					"",
					"event: token",
					'data: {"index":1,"text":"answer "}',
					"",
					"event: done",
					"data: {}",
					"",
				].join("\n"),
				{
					status: 200,
					headers: { "Content-Type": "text/event-stream" },
				},
			),
		);
		const httpClient = {
			get: vi.fn(),
			post: vi.fn(),
		} as unknown as HttpClient;
		const repository = createRepository(httpClient);
		const tokens: Array<{ index: number; text: string }> = [];
		const onDone = vi.fn();
		const onError = vi.fn();

		await repository.streamQuestion(
			"550e8400-e29b-41d4-a716-446655440000",
			"Why did Rome fall?",
			{
				onToken: (token) => {
					tokens.push(token);
				},
				onDone,
				onError,
			},
		);

		expect(tokens).toEqual([
			{ index: 0, text: "Mock " },
			{ index: 1, text: "answer " },
		]);
		expect(onDone).toHaveBeenCalledOnce();
		expect(onError).not.toHaveBeenCalled();

		fetchMock.mockRestore();
	});

	it("calls onError for non-2xx HTTP responses", async () => {
		const fetchMock = vi
			.spyOn(globalThis, "fetch")
			.mockResolvedValue(new Response("bad request", { status: 400 }));
		const httpClient = {
			get: vi.fn(),
			post: vi.fn(),
		} as unknown as HttpClient;
		const repository = createRepository(httpClient);
		const onError = vi.fn();

		await repository.streamQuestion(
			"550e8400-e29b-41d4-a716-446655440000",
			"Why did Rome fall?",
			{
				onToken: vi.fn(),
				onDone: vi.fn(),
				onError,
			},
		);

		expect(onError).toHaveBeenCalledWith(expect.any(ApiError));

		fetchMock.mockRestore();
	});

	it("calls onError for malformed token events", async () => {
		const fetchMock = vi.spyOn(globalThis, "fetch").mockResolvedValue(
			new Response("event: token\ndata: not-json\n\n", {
				status: 200,
				headers: { "Content-Type": "text/event-stream" },
			}),
		);
		const httpClient = {
			get: vi.fn(),
			post: vi.fn(),
		} as unknown as HttpClient;
		const repository = createRepository(httpClient);
		const onError = vi.fn();

		await repository.streamQuestion(
			"550e8400-e29b-41d4-a716-446655440000",
			"Why did Rome fall?",
			{
				onToken: vi.fn(),
				onDone: vi.fn(),
				onError,
			},
		);

		expect(onError).toHaveBeenCalledWith(
			expect.objectContaining({ message: "Malformed SSE token payload" }),
		);

		fetchMock.mockRestore();
	});
});
