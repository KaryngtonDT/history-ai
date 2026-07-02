import {
	SHADOW_KNOWLEDGE_GAPS_PATH,
	SHADOW_KNOWLEDGE_GRAPH_PATH,
	SHADOW_KNOWLEDGE_NODE_PATH,
	SHADOW_KNOWLEDGE_PATH_PATH,
	SHADOW_KNOWLEDGE_REBUILD_PATH,
	SHADOW_KNOWLEDGE_RELATED_PATH,
	SHADOW_KNOWLEDGE_RESET_PATH,
	SHADOW_KNOWLEDGE_SEARCH_PATH,
} from "@/config/api";
import type { HttpClient } from "@/services/http/HttpClient";
import type { ShadowKnowledgeRepository } from "./ShadowKnowledgeRepository";
import type {
	KnowledgeGapsResponse,
	KnowledgeGraph,
	KnowledgeNodeDetail,
	KnowledgePathResponse,
	KnowledgeRelatedResponse,
	KnowledgeSearchRequest,
	KnowledgeSearchResult,
} from "./types";

export class HttpShadowKnowledgeRepository implements ShadowKnowledgeRepository {
	private readonly httpClient: HttpClient;

	constructor(httpClient: HttpClient) {
		this.httpClient = httpClient;
	}

	getGraph(scopeKey?: string): Promise<KnowledgeGraph> {
		const query = scopeKey ? `?scopeKey=${encodeURIComponent(scopeKey)}` : "";

		return this.httpClient.get<KnowledgeGraph>(
			`${SHADOW_KNOWLEDGE_GRAPH_PATH}${query}`,
		);
	}

	getNode(id: string, scopeKey?: string): Promise<KnowledgeNodeDetail> {
		const query = scopeKey ? `?scopeKey=${encodeURIComponent(scopeKey)}` : "";

		return this.httpClient.get<KnowledgeNodeDetail>(
			`${SHADOW_KNOWLEDGE_NODE_PATH(id)}${query}`,
		);
	}

	getPath(scopeKey?: string): Promise<KnowledgePathResponse> {
		const query = scopeKey ? `?scopeKey=${encodeURIComponent(scopeKey)}` : "";

		return this.httpClient.get<KnowledgePathResponse>(
			`${SHADOW_KNOWLEDGE_PATH_PATH}${query}`,
		);
	}

	getGaps(
		goalKey = "kubernetes",
		scopeKey?: string,
	): Promise<KnowledgeGapsResponse> {
		const params = new URLSearchParams({ goalKey });
		if (scopeKey) {
			params.set("scopeKey", scopeKey);
		}

		return this.httpClient.get<KnowledgeGapsResponse>(
			`${SHADOW_KNOWLEDGE_GAPS_PATH}?${params.toString()}`,
		);
	}

	getRelated(key: string, scopeKey?: string): Promise<KnowledgeRelatedResponse> {
		const params = new URLSearchParams({ key });
		if (scopeKey) {
			params.set("scopeKey", scopeKey);
		}

		return this.httpClient.get<KnowledgeRelatedResponse>(
			`${SHADOW_KNOWLEDGE_RELATED_PATH}?${params.toString()}`,
		);
	}

	search(request: KnowledgeSearchRequest): Promise<KnowledgeSearchResult> {
		return this.httpClient.post<KnowledgeSearchResult>(
			SHADOW_KNOWLEDGE_SEARCH_PATH,
			request,
		);
	}

	rebuild(scopeKey?: string): Promise<KnowledgeGraph> {
		return this.httpClient.post<KnowledgeGraph>(
			SHADOW_KNOWLEDGE_REBUILD_PATH,
			scopeKey ? { scopeKey } : {},
		);
	}

	reset(scopeKey?: string): Promise<KnowledgeGraph> {
		return this.httpClient.post<KnowledgeGraph>(
			SHADOW_KNOWLEDGE_RESET_PATH,
			scopeKey ? { scopeKey } : {},
		);
	}
}
