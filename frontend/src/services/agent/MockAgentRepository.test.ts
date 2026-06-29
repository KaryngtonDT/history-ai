import { describe, expect, it } from "vitest";
import { MockAgentRepository } from "./MockAgentRepository";

describe("MockAgentRepository", () => {
	it("returns default plan with semantic_search and multi_document_chat", async () => {
		const repository = new MockAgentRepository();

		const execution = await repository.runAgent(
			"550e8400-e29b-41d4-a716-446655440000",
			"What is Rome?",
		);

		expect(execution.plan.map((step) => step.tool)).toEqual([
			"semantic_search",
			"multi_document_chat",
		]);
		expect(execution.steps.map((step) => step.tool)).toEqual([
			"semantic_search",
			"multi_document_chat",
		]);
		expect(execution.finalSummary).toBe("Agent workflow completed.");
	});

	it("includes knowledge_graph for comparison questions", async () => {
		const repository = new MockAgentRepository();

		const execution = await repository.runAgent(
			"550e8400-e29b-41d4-a716-446655440000",
			"Compare Rome versus Byzantium",
		);

		expect(execution.plan.map((step) => step.tool)).toEqual([
			"semantic_search",
			"knowledge_graph",
			"multi_document_chat",
		]);
	});

	it("includes conversation_memory for memory questions", async () => {
		const repository = new MockAgentRepository();

		const execution = await repository.runAgent(
			"550e8400-e29b-41d4-a716-446655440000",
			"What did we discuss earlier in this conversation?",
		);

		expect(execution.plan.map((step) => step.tool)).toEqual([
			"semantic_search",
			"conversation_memory",
			"multi_document_chat",
		]);
	});
});
