import { describe, expect, it, vi } from "vitest";
import type { HttpClient } from "@/services/http/HttpClient";
import { ApiError } from "@/shared/errors";
import { HttpAgentRepository } from "./HttpAgentRepository";
import { EMPTY_AGENT_EXECUTION } from "./types";

describe("HttpAgentRepository", () => {
	it("uses POST /api/contents/{contentId}/agent/run", async () => {
		const post = vi.fn().mockResolvedValue({
			plan: [],
			steps: [],
			finalSummary: "",
		});
		const httpClient = { get: vi.fn(), post } as unknown as HttpClient;
		const repository = new HttpAgentRepository(httpClient);

		await repository.runAgent(
			"550e8400-e29b-41d4-a716-446655440000",
			"What is Rome?",
		);

		expect(post).toHaveBeenCalledWith(
			"/api/contents/550e8400-e29b-41d4-a716-446655440000/agent/run",
			{ question: "What is Rome?" },
		);
	});

	it("includes conversationId when provided", async () => {
		const post = vi.fn().mockResolvedValue({
			plan: [],
			steps: [],
			finalSummary: "",
		});
		const httpClient = { get: vi.fn(), post } as unknown as HttpClient;
		const repository = new HttpAgentRepository(httpClient);

		await repository.runAgent(
			"550e8400-e29b-41d4-a716-446655440000",
			"What is Rome?",
			"550e8400-e29b-41d4-a716-446655440001",
		);

		expect(post).toHaveBeenCalledWith(
			"/api/contents/550e8400-e29b-41d4-a716-446655440000/agent/run",
			{
				question: "What is Rome?",
				conversationId: "550e8400-e29b-41d4-a716-446655440001",
			},
		);
	});

	it("maps API DTO to agent execution preserving order", async () => {
		const post = vi.fn().mockResolvedValue({
			plan: [
				{
					order: 0,
					tool: "semantic_search",
					description: "Retrieve relevant document chunks for the question",
				},
				{
					order: 1,
					tool: "multi_document_chat",
					description: "Generate the final answer from gathered context",
				},
			],
			steps: [
				{
					order: 0,
					tool: "semantic_search",
					status: "completed",
					summary: "Semantic search prepared.",
					metadata: { resultCount: 2, topScore: 0.91 },
				},
				{
					order: 1,
					tool: "multi_document_chat",
					status: "completed",
					summary: "Multi-document chat prepared.",
					metadata: { requiresConversation: true },
				},
			],
			finalSummary: "Agent workflow completed.",
			metadata: {
				resultCount: 2,
				topScore: 0.91,
				requiresConversation: true,
			},
		});
		const httpClient = { get: vi.fn(), post } as unknown as HttpClient;
		const repository = new HttpAgentRepository(httpClient);

		const result = await repository.runAgent(
			"550e8400-e29b-41d4-a716-446655440000",
			"What is Rome?",
		);

		expect(result).toEqual({
			plan: [
				{
					order: 0,
					tool: "semantic_search",
					description: "Retrieve relevant document chunks for the question",
				},
				{
					order: 1,
					tool: "multi_document_chat",
					description: "Generate the final answer from gathered context",
				},
			],
			steps: [
				{
					order: 0,
					tool: "semantic_search",
					status: "completed",
					summary: "Semantic search prepared.",
					metadata: { resultCount: 2, topScore: 0.91 },
				},
				{
					order: 1,
					tool: "multi_document_chat",
					status: "completed",
					summary: "Multi-document chat prepared.",
					metadata: { requiresConversation: true },
				},
			],
			finalSummary: "Agent workflow completed.",
			metadata: {
				resultCount: 2,
				topScore: 0.91,
				requiresConversation: true,
			},
		});
	});

	it("returns empty execution when API responds with 400", async () => {
		const post = vi.fn().mockRejectedValue(new ApiError("POST failed", 400));
		const httpClient = { get: vi.fn(), post } as unknown as HttpClient;
		const repository = new HttpAgentRepository(httpClient);

		const result = await repository.runAgent(
			"550e8400-e29b-41d4-a716-446655440000",
			"What is Rome?",
		);

		expect(result).toEqual(EMPTY_AGENT_EXECUTION);
	});

	it("propagates non-400 HTTP errors", async () => {
		const post = vi.fn().mockRejectedValue(new ApiError("POST failed", 500));
		const httpClient = { get: vi.fn(), post } as unknown as HttpClient;
		const repository = new HttpAgentRepository(httpClient);

		await expect(
			repository.runAgent(
				"550e8400-e29b-41d4-a716-446655440000",
				"What is Rome?",
			),
		).rejects.toBeInstanceOf(ApiError);
	});
});
