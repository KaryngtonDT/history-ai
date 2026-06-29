import { describe, expect, it, vi } from "vitest";
import type { HttpClient } from "@/services/http/HttpClient";
import { ApiError } from "@/shared/errors";
import { HttpConversationRepository } from "./HttpConversationRepository";
import { EMPTY_CONVERSATION, EMPTY_CONVERSATION_CHAT_RESULT } from "./types";

const BASE_URL = "http://localhost:8000";
const contentId = "550e8400-e29b-41d4-a716-446655440000";
const conversationId = "550e8400-e29b-41d4-a716-446655440001";
const otherContentId = "550e8400-e29b-41d4-a716-446655440099";
const conversationDocuments = [{ contentId }];

function createRepository(httpClient: HttpClient): HttpConversationRepository {
	return new HttpConversationRepository(httpClient, BASE_URL);
}

describe("HttpConversationRepository", () => {
	it("uses POST /api/contents/{contentId}/conversations/{conversationId}/chat", async () => {
		const post = vi.fn().mockResolvedValue({
			conversation: {
				id: conversationId,
				contentId,
				messages: [
					{ role: "user", text: "Why did Rome fall?" },
					{
						role: "assistant",
						text: "Mock answer based on retrieved context.",
					},
				],
				documents: conversationDocuments,
			},
			answer: {
				answer: "Mock answer based on retrieved context.",
				sources: [],
				citations: [],
			},
		});
		const httpClient = {
			get: vi.fn(),
			post,
			put: vi.fn(),
		} as unknown as HttpClient;
		const repository = createRepository(httpClient);

		await repository.askQuestion(
			contentId,
			conversationId,
			"Why did Rome fall?",
		);

		expect(post).toHaveBeenCalledWith(
			`/api/contents/${contentId}/conversations/${conversationId}/chat`,
			{ question: "Why did Rome fall?" },
		);
	});

	it("maps API DTO to conversation chat result", async () => {
		const post = vi.fn().mockResolvedValue({
			conversation: {
				id: conversationId,
				contentId,
				messages: [
					{ role: "user", text: "Why did Rome fall?" },
					{
						role: "assistant",
						text: "Mock answer based on retrieved context [1].",
					},
				],
				documents: conversationDocuments,
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
		const httpClient = {
			get: vi.fn(),
			post,
			put: vi.fn(),
		} as unknown as HttpClient;
		const repository = createRepository(httpClient);

		const response = await repository.askQuestion(
			contentId,
			conversationId,
			"Why did Rome fall?",
		);

		expect(response.conversation.messages).toHaveLength(2);
		expect(response.conversation.documents).toEqual(conversationDocuments);
		expect(response.answer.citations).toHaveLength(1);
	});

	it("returns empty result for HTTP 400", async () => {
		const post = vi
			.fn()
			.mockRejectedValue(new ApiError("Invalid request", 400));
		const httpClient = {
			get: vi.fn(),
			post,
			put: vi.fn(),
		} as unknown as HttpClient;
		const repository = createRepository(httpClient);

		const response = await repository.askQuestion(
			contentId,
			conversationId,
			"Why did Rome fall?",
		);

		expect(response).toEqual(EMPTY_CONVERSATION_CHAT_RESULT);
	});

	it("returns empty result for malformed API payload", async () => {
		const post = vi.fn().mockResolvedValue({
			conversation: {
				id: "invalid",
				contentId,
				messages: [],
			},
			answer: {
				answer: "Mock answer.",
				sources: [],
			},
		});
		const httpClient = {
			get: vi.fn(),
			post,
			put: vi.fn(),
		} as unknown as HttpClient;
		const repository = createRepository(httpClient);

		const response = await repository.askQuestion(
			contentId,
			conversationId,
			"Why did Rome fall?",
		);

		expect(response).toEqual(EMPTY_CONVERSATION_CHAT_RESULT);
	});

	it("uses PUT /api/conversations/{conversationId}/documents", async () => {
		const put = vi.fn().mockResolvedValue({
			conversation: {
				id: conversationId,
				contentId: otherContentId,
				messages: [{ role: "user", text: "Earlier question" }],
				documents: [{ contentId: otherContentId }, { contentId }],
			},
		});
		const httpClient = {
			get: vi.fn(),
			post: vi.fn(),
			put,
		} as unknown as HttpClient;
		const repository = createRepository(httpClient);

		await repository.updateDocuments(conversationId, [
			otherContentId,
			contentId,
		]);

		expect(put).toHaveBeenCalledWith(
			`/api/conversations/${conversationId}/documents`,
			{ contentIds: [otherContentId, contentId] },
		);
	});

	it("maps update documents API DTO to conversation", async () => {
		const put = vi.fn().mockResolvedValue({
			conversation: {
				id: conversationId,
				contentId: otherContentId,
				messages: [{ role: "user", text: "Earlier question" }],
				documents: [{ contentId: otherContentId }, { contentId }],
			},
		});
		const httpClient = {
			get: vi.fn(),
			post: vi.fn(),
			put,
		} as unknown as HttpClient;
		const repository = createRepository(httpClient);

		const response = await repository.updateDocuments(conversationId, [
			otherContentId,
			contentId,
		]);

		expect(response.documents).toEqual([
			{ contentId: otherContentId },
			{ contentId },
		]);
	});

	it("returns empty conversation for HTTP 404 on update documents", async () => {
		const put = vi
			.fn()
			.mockRejectedValue(new ApiError("Conversation not found", 404));
		const httpClient = {
			get: vi.fn(),
			post: vi.fn(),
			put,
		} as unknown as HttpClient;
		const repository = createRepository(httpClient);

		const response = await repository.updateDocuments(conversationId, [
			contentId,
		]);

		expect(response).toEqual(EMPTY_CONVERSATION);
	});

	it("uses fetch for POST /api/contents/{contentId}/conversations/{conversationId}/chat/stream", async () => {
		const fetchMock = vi.spyOn(globalThis, "fetch").mockResolvedValue(
			new Response(
				[
					"event: token",
					'data: {"index":0,"text":"Mock "}',
					"",
					"event: conversation",
					`data: ${JSON.stringify({
						conversation: {
							id: conversationId,
							contentId,
							messages: [
								{ role: "user", text: "Why did Rome fall?" },
								{
									role: "assistant",
									text: "Mock answer based on retrieved context.",
								},
							],
							documents: conversationDocuments,
						},
					})}`,
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
			put: vi.fn(),
		} as unknown as HttpClient;
		const repository = createRepository(httpClient);
		const onToken = vi.fn();
		const onConversation = vi.fn();
		const onDone = vi.fn();
		const onError = vi.fn();

		await repository.streamQuestion(
			contentId,
			conversationId,
			"Why did Rome fall?",
			{ onToken, onConversation, onDone, onError },
		);

		expect(fetchMock).toHaveBeenCalledWith(
			`http://localhost:8000/api/contents/${contentId}/conversations/${conversationId}/chat/stream`,
			expect.objectContaining({
				method: "POST",
				body: JSON.stringify({ question: "Why did Rome fall?" }),
			}),
		);
		expect(onToken).toHaveBeenCalledWith({ index: 0, text: "Mock " });
		expect(onConversation).toHaveBeenCalledWith(
			expect.objectContaining({ id: conversationId }),
		);
		expect(onDone).toHaveBeenCalledOnce();
		expect(onError).not.toHaveBeenCalled();

		fetchMock.mockRestore();
	});

	it("parses token events in order before conversation and done", async () => {
		const fetchMock = vi.spyOn(globalThis, "fetch").mockResolvedValue(
			new Response(
				[
					"event: token",
					'data: {"index":0,"text":"Mock "}',
					"",
					"event: token",
					'data: {"index":1,"text":"answer "}',
					"",
					"event: conversation",
					`data: ${JSON.stringify({
						conversation: {
							id: conversationId,
							contentId,
							messages: [
								{ role: "user", text: "Why did Rome fall?" },
								{ role: "assistant", text: "Mock answer" },
							],
							documents: conversationDocuments,
						},
					})}`,
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
			put: vi.fn(),
		} as unknown as HttpClient;
		const repository = createRepository(httpClient);
		const tokens: Array<{ index: number; text: string }> = [];
		const eventOrder: string[] = [];
		const onDone = vi.fn();
		const onError = vi.fn();

		await repository.streamQuestion(
			contentId,
			conversationId,
			"Why did Rome fall?",
			{
				onToken: (token) => {
					eventOrder.push("token");
					tokens.push(token);
				},
				onConversation: () => {
					eventOrder.push("conversation");
				},
				onDone: () => {
					eventOrder.push("done");
					onDone();
				},
				onError,
			},
		);

		expect(tokens).toEqual([
			{ index: 0, text: "Mock " },
			{ index: 1, text: "answer " },
		]);
		expect(eventOrder).toEqual(["token", "token", "conversation", "done"]);
		expect(onDone).toHaveBeenCalledOnce();
		expect(onError).not.toHaveBeenCalled();

		fetchMock.mockRestore();
	});

	it("calls onError for non-2xx stream responses", async () => {
		const fetchMock = vi
			.spyOn(globalThis, "fetch")
			.mockResolvedValue(new Response("bad request", { status: 400 }));
		const httpClient = {
			get: vi.fn(),
			post: vi.fn(),
			put: vi.fn(),
		} as unknown as HttpClient;
		const repository = createRepository(httpClient);
		const onError = vi.fn();

		await repository.streamQuestion(
			contentId,
			conversationId,
			"Why did Rome fall?",
			{
				onToken: vi.fn(),
				onConversation: vi.fn(),
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
			put: vi.fn(),
		} as unknown as HttpClient;
		const repository = createRepository(httpClient);
		const onError = vi.fn();

		await repository.streamQuestion(
			contentId,
			conversationId,
			"Why did Rome fall?",
			{
				onToken: vi.fn(),
				onConversation: vi.fn(),
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
