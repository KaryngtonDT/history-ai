export type KnowledgeNodeType =
	| "concept"
	| "technology"
	| "framework"
	| "mission"
	| "skill";

export type KnowledgeEdgeType =
	| "prerequisite"
	| "introduces"
	| "depends_on"
	| "related_to"
	| "used_by"
	| "extends";

export type KnowledgeConfidence = "low" | "medium" | "high";

export interface KnowledgeNode {
	key: string;
	label: string;
	type: KnowledgeNodeType;
	explanation: string;
	sources: string[];
}

export interface KnowledgeEdge {
	id: string;
	fromKey: string;
	toKey: string;
	type: KnowledgeEdgeType;
	label: string;
	reason: string;
	source: string;
	confidence: KnowledgeConfidence;
}

export interface KnowledgeMastery {
	nodeKey: string;
	percent: number;
	exposureCount: number;
	exerciseCount: number;
	explanationCount: number;
	videoIds: string[];
	confidence: KnowledgeConfidence;
	mastered: boolean;
}

export interface KnowledgePathStep {
	key: string;
	label: string;
}

export interface KnowledgePath {
	key: string;
	label: string;
	steps: KnowledgePathStep[];
}

export interface KnowledgeGap {
	conceptKey: string;
	label: string;
	masteryPercent: number;
	missing: boolean;
	recommended: string;
	reason: string;
}

export interface KnowledgeRadar {
	goalKey: string;
	goalLabel: string;
	readinessPercent: number;
	gaps: KnowledgeGap[];
}

export interface KnowledgeGraph {
	id: string;
	scopeKey: string;
	graphEnabled: boolean;
	nodes: KnowledgeNode[];
	edges: KnowledgeEdge[];
	masteries: KnowledgeMastery[];
	paths: KnowledgePath[];
}

export interface KnowledgeNodeDetail {
	node: KnowledgeNode;
	mastery: KnowledgeMastery | null;
	related: KnowledgeEdge[];
	gaps: KnowledgeGap[];
}

export interface KnowledgeSearchResult {
	query: string;
	nodes: Array<Pick<KnowledgeNode, "key" | "label" | "type">>;
	edges: Array<Pick<KnowledgeEdge, "id" | "label" | "fromKey" | "toKey">>;
	total: number;
}

export interface KnowledgeSearchRequest {
	query: string;
	scopeKey?: string;
}

export interface KnowledgeGapsResponse {
	scopeKey: string;
	radar: KnowledgeRadar;
}

export interface KnowledgeRelatedResponse {
	scopeKey: string;
	key: string;
	related: KnowledgeEdge[];
	node: KnowledgeNode | null;
}

export interface KnowledgePathResponse {
	scopeKey: string;
	paths: KnowledgePath[];
}
