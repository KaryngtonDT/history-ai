import type { AgentExecutionStep, AgentTool } from "@/services/agent/types";
import { formatAgentToolLabel } from "@/services/agent/types";

export interface AgentMetadataEntry {
	label: string;
	value: string;
}

export interface AgentMetadataSection {
	tool: AgentTool;
	title: string;
	entries: AgentMetadataEntry[];
}

const METADATA_LABELS: Partial<Record<AgentTool, Record<string, string>>> = {
	semantic_search: {
		resultCount: "Chunks",
		topScore: "Top score",
	},
	knowledge_graph: {
		nodeCount: "Nodes",
		edgeCount: "Edges",
	},
	conversation_memory: {
		messageCount: "Messages",
		userMessages: "User messages",
		assistantMessages: "Assistant messages",
	},
	multi_document_chat: {
		sourceCount: "Sources",
		citationCount: "Citations",
		requiresConversation: "Requires conversation",
	},
};

function formatMetadataValue(key: string, value: unknown): string {
	if (typeof value === "boolean") {
		return value ? "Yes" : "No";
	}

	if (typeof value === "number") {
		if (key === "topScore") {
			return value.toFixed(2);
		}

		return String(value);
	}

	return String(value);
}

function buildEntriesForStep(step: AgentExecutionStep): AgentMetadataEntry[] {
	const labels = METADATA_LABELS[step.tool] ?? {};
	const entries: AgentMetadataEntry[] = [];

	for (const [key, value] of Object.entries(step.metadata ?? {})) {
		const label = labels[key] ?? key;

		entries.push({
			label,
			value: formatMetadataValue(key, value),
		});
	}

	return entries;
}

export function buildAgentMetadataSections(
	steps: AgentExecutionStep[],
): AgentMetadataSection[] {
	const sections: AgentMetadataSection[] = [];

	for (const step of steps) {
		const entries = buildEntriesForStep(step);

		if (entries.length === 0) {
			continue;
		}

		sections.push({
			tool: step.tool,
			title: formatAgentToolLabel(step.tool),
			entries,
		});
	}

	return sections;
}
