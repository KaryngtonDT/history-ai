import { describe, expect, it } from "vitest";
import type { AgentExecutionStep } from "@/services/agent/types";
import { buildAgentMetadataSections } from "./agentMetadataLabels";

describe("buildAgentMetadataSections", () => {
	it("maps semantic search metadata labels", () => {
		const steps: AgentExecutionStep[] = [
			{
				order: 0,
				tool: "semantic_search",
				status: "completed",
				summary: "Semantic search found 4 relevant chunks.",
				metadata: { resultCount: 4, topScore: 0.92 },
			},
		];

		expect(buildAgentMetadataSections(steps)).toEqual([
			{
				tool: "semantic_search",
				title: "Semantic Search",
				entries: [
					{ label: "Chunks", value: "4" },
					{ label: "Top score", value: "0.92" },
				],
			},
		]);
	});

	it("maps graph, memory, and chat metadata labels", () => {
		const steps: AgentExecutionStep[] = [
			{
				order: 0,
				tool: "knowledge_graph",
				status: "completed",
				summary: "Knowledge graph contains 12 nodes and 18 relationships.",
				metadata: { nodeCount: 12, edgeCount: 18 },
			},
			{
				order: 1,
				tool: "conversation_memory",
				status: "completed",
				summary: "Conversation memory contains 9 messages.",
				metadata: { messageCount: 9 },
			},
			{
				order: 2,
				tool: "multi_document_chat",
				status: "completed",
				summary: "Multi-document chat generated an answer.",
				metadata: { sourceCount: 3, citationCount: 3 },
			},
		];

		expect(buildAgentMetadataSections(steps)).toEqual([
			{
				tool: "knowledge_graph",
				title: "Knowledge Graph",
				entries: [
					{ label: "Nodes", value: "12" },
					{ label: "Edges", value: "18" },
				],
			},
			{
				tool: "conversation_memory",
				title: "Conversation Memory",
				entries: [{ label: "Messages", value: "9" }],
			},
			{
				tool: "multi_document_chat",
				title: "Multi-Document Chat",
				entries: [
					{ label: "Sources", value: "3" },
					{ label: "Citations", value: "3" },
				],
			},
		]);
	});

	it("skips steps without metadata", () => {
		const steps: AgentExecutionStep[] = [
			{
				order: 0,
				tool: "semantic_search",
				status: "completed",
				summary: "Semantic search found no relevant chunks.",
				metadata: {},
			},
		];

		expect(buildAgentMetadataSections(steps)).toEqual([]);
	});
});
