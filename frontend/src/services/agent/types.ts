export type AgentTool =
	| "semantic_search"
	| "knowledge_graph"
	| "conversation_memory"
	| "multi_document_chat";

export type AgentExecutionStatus = "completed" | "skipped" | "failed";

export interface AgentPlanStep {
	order: number;
	tool: AgentTool;
	description: string;
}

export interface AgentExecutionStep {
	order: number;
	tool: AgentTool;
	status: AgentExecutionStatus;
	summary: string;
	metadata: AgentMetadata;
}

export type AgentMetadata = Record<string, unknown>;

export interface AgentExecution {
	plan: AgentPlanStep[];
	steps: AgentExecutionStep[];
	finalSummary: string;
	metadata: AgentMetadata;
}

export interface AgentPlanStepApiDto {
	order: number;
	tool: string;
	description: string;
}

export interface AgentExecutionStepApiDto {
	order: number;
	tool: string;
	status: string;
	summary: string;
	metadata?: AgentMetadata;
}

export interface AgentExecutionApiDto {
	plan: AgentPlanStepApiDto[];
	steps: AgentExecutionStepApiDto[];
	finalSummary: string;
	metadata?: AgentMetadata;
}

export interface RunAgentRequestDto {
	question: string;
	conversationId?: string;
}

export const EMPTY_AGENT_EXECUTION: AgentExecution = {
	plan: [],
	steps: [],
	finalSummary: "",
	metadata: {},
};

const AGENT_TOOLS = new Set<AgentTool>([
	"semantic_search",
	"knowledge_graph",
	"conversation_memory",
	"multi_document_chat",
]);

const AGENT_STATUSES = new Set<AgentExecutionStatus>([
	"completed",
	"skipped",
	"failed",
]);

const COMPARISON_KEYWORDS = [
	"compare",
	"versus",
	"vs",
	"difference",
	"comparez",
	"différence",
	"unterschied",
	"vergleichen",
];

const MEMORY_KEYWORDS = [
	"previous",
	"earlier",
	"history",
	"conversation",
	"précédent",
	"historique",
	"vorher",
	"verlauf",
];

const STEP_METADATA: Partial<Record<AgentTool, AgentMetadata>> = {
	semantic_search: { resultCount: 2, topScore: 0.91 },
	knowledge_graph: { nodeCount: 3, edgeCount: 3 },
	conversation_memory: { messageCount: 0 },
	multi_document_chat: { requiresConversation: true },
};

const STEP_SUMMARIES: Record<AgentTool, string> = {
	semantic_search: "Semantic search prepared.",
	knowledge_graph: "Knowledge graph exploration prepared.",
	conversation_memory: "Conversation memory prepared.",
	multi_document_chat: "Multi-document chat prepared.",
};

const PLAN_DESCRIPTIONS: Record<AgentTool, string> = {
	semantic_search: "Retrieve relevant document chunks for the question",
	knowledge_graph: "Explore artifact relationships relevant to the question",
	conversation_memory: "Include prior conversation context",
	multi_document_chat: "Generate the final answer from gathered context",
};

function normalizeAgentTool(tool: string): AgentTool {
	if (AGENT_TOOLS.has(tool as AgentTool)) {
		return tool as AgentTool;
	}

	return "semantic_search";
}

function normalizeAgentStatus(status: string): AgentExecutionStatus {
	if (AGENT_STATUSES.has(status as AgentExecutionStatus)) {
		return status as AgentExecutionStatus;
	}

	return "completed";
}

function containsKeyword(question: string, keywords: string[]): boolean {
	const normalizedQuestion = question.toLocaleLowerCase();

	return keywords.some((keyword) =>
		normalizedQuestion.includes(keyword.toLocaleLowerCase()),
	);
}

export function buildMockAgentExecution(question: string): AgentExecution {
	const planTools: AgentTool[] = ["semantic_search"];

	if (containsKeyword(question, COMPARISON_KEYWORDS)) {
		planTools.push("knowledge_graph");
	}

	if (containsKeyword(question, MEMORY_KEYWORDS)) {
		planTools.push("conversation_memory");
	}

	planTools.push("multi_document_chat");

	const plan: AgentPlanStep[] = planTools.map((tool, order) => ({
		order,
		tool,
		description: PLAN_DESCRIPTIONS[tool],
	}));

	const steps: AgentExecutionStep[] = planTools.map((tool, order) => ({
		order,
		tool,
		status: "completed",
		summary: STEP_SUMMARIES[tool],
		metadata: { ...(STEP_METADATA[tool] ?? {}) },
	}));

	const metadata: AgentMetadata = {};

	for (const step of steps) {
		Object.assign(metadata, step.metadata);
	}

	return {
		plan,
		steps,
		finalSummary: "Agent workflow completed.",
		metadata,
	};
}

export function mapAgentExecutionFromApi(
	dto: AgentExecutionApiDto,
): AgentExecution {
	return {
		plan: dto.plan.map((step) => ({
			order: step.order,
			tool: normalizeAgentTool(step.tool),
			description: step.description,
		})),
		steps: dto.steps.map((step) => ({
			order: step.order,
			tool: normalizeAgentTool(step.tool),
			status: normalizeAgentStatus(step.status),
			summary: step.summary,
			metadata: step.metadata ?? {},
		})),
		finalSummary: dto.finalSummary,
		metadata: dto.metadata ?? {},
	};
}

export function formatAgentToolLabel(tool: AgentTool): string {
	switch (tool) {
		case "semantic_search":
			return "Semantic Search";
		case "knowledge_graph":
			return "Knowledge Graph";
		case "conversation_memory":
			return "Conversation Memory";
		case "multi_document_chat":
			return "Multi-Document Chat";
	}
}
