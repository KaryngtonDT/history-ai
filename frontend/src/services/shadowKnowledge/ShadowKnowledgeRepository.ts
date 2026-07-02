import type {
	KnowledgeGapsResponse,
	KnowledgeGraph,
	KnowledgeNodeDetail,
	KnowledgePathResponse,
	KnowledgeRelatedResponse,
	KnowledgeSearchRequest,
	KnowledgeSearchResult,
} from "./types";

export interface ShadowKnowledgeRepository {
	getGraph(scopeKey?: string): Promise<KnowledgeGraph>;
	getNode(id: string, scopeKey?: string): Promise<KnowledgeNodeDetail>;
	getPath(scopeKey?: string): Promise<KnowledgePathResponse>;
	getGaps(goalKey?: string, scopeKey?: string): Promise<KnowledgeGapsResponse>;
	getRelated(key: string, scopeKey?: string): Promise<KnowledgeRelatedResponse>;
	search(request: KnowledgeSearchRequest): Promise<KnowledgeSearchResult>;
	rebuild(scopeKey?: string): Promise<KnowledgeGraph>;
	reset(scopeKey?: string): Promise<KnowledgeGraph>;
}
