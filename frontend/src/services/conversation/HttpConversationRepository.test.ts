import { describe, expect, it, vi } from "vitest";
import type { HttpClient } from "@/services/http/HttpClient";
import { ApiError } from "@/shared/errors";
import { HttpConversationRepository } from "./HttpConversationRepository";
import { EMPTY_CONVERSATION, EMPTY_CONVERSATION_CHAT_RESULT } from "./types";

const contentId = "550e8400-e29b-41d4-a716-446655440000";
const conversationId = "550e8400-e29b-41d4-a716-446655440001";
const otherContentId = "550e8400-e29b-41d4-a716-446655440099";
const conversationDocuments = [{ contentId }];

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
		const repository = new HttpConversationRepository(httpClient);

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
		const repository = new HttpConversationRepository(httpClient);

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
		const repository = new HttpConversationRepository(httpClient);

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
		const repository = new HttpConversationRepository(httpClient);

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
		const repository = new HttpConversationRepository(httpClient);

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
		const repository = new HttpConversationRepository(httpClient);

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
		const repository = new HttpConversationRepository(httpClient);

		const response = await repository.updateDocuments(conversationId, [
			contentId,
		]);

		expect(response).toEqual(EMPTY_CONVERSATION);
	});
});
