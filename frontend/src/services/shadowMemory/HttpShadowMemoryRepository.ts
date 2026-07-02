import {
	SHADOW_MEMORY_CONCEPTS_PATH,
	SHADOW_MEMORY_CONNECTIONS_PATH,
	SHADOW_MEMORY_JOURNEY_PATH,
	SHADOW_MEMORY_MILESTONES_PATH,
	SHADOW_MEMORY_RESET_PATH,
	SHADOW_MEMORY_SEARCH_PATH,
	SHADOW_MEMORY_TIMELINE_PATH,
	SHADOW_MEMORY_VOCABULARY_PATH,
} from "@/config/api";
import type { HttpClient } from "@/services/http/HttpClient";
import type { ShadowMemoryRepository } from "./ShadowMemoryRepository";
import type {
	LearningJourney,
	MemorySearchRequest,
	MemorySearchResult,
	MemoryTimeline,
} from "./types";

export class HttpShadowMemoryRepository implements ShadowMemoryRepository {
	private readonly httpClient: HttpClient;

	constructor(httpClient: HttpClient) {
		this.httpClient = httpClient;
	}

	getTimeline(scopeKey?: string): Promise<MemoryTimeline> {
		const query = scopeKey ? `?scopeKey=${encodeURIComponent(scopeKey)}` : "";

		return this.httpClient.get<MemoryTimeline>(
			`${SHADOW_MEMORY_TIMELINE_PATH}${query}`,
		);
	}

	getConcepts(scopeKey?: string): Promise<{
		scopeKey: string;
		concepts: MemoryTimeline["concepts"];
	}> {
		const query = scopeKey ? `?scopeKey=${encodeURIComponent(scopeKey)}` : "";

		return this.httpClient.get<{
			scopeKey: string;
			concepts: MemoryTimeline["concepts"];
		}>(`${SHADOW_MEMORY_CONCEPTS_PATH}${query}`);
	}

	getVocabulary(scopeKey?: string): Promise<{
		scopeKey: string;
		vocabulary: MemoryTimeline["vocabulary"];
	}> {
		const query = scopeKey ? `?scopeKey=${encodeURIComponent(scopeKey)}` : "";

		return this.httpClient.get<{
			scopeKey: string;
			vocabulary: MemoryTimeline["vocabulary"];
		}>(`${SHADOW_MEMORY_VOCABULARY_PATH}${query}`);
	}

	getMilestones(scopeKey?: string): Promise<{
		scopeKey: string;
		milestones: MemoryTimeline["milestones"];
	}> {
		const query = scopeKey ? `?scopeKey=${encodeURIComponent(scopeKey)}` : "";

		return this.httpClient.get<{
			scopeKey: string;
			milestones: MemoryTimeline["milestones"];
		}>(`${SHADOW_MEMORY_MILESTONES_PATH}${query}`);
	}

	getConnections(scopeKey?: string): Promise<{
		scopeKey: string;
		connections: MemoryTimeline["connections"];
	}> {
		const query = scopeKey ? `?scopeKey=${encodeURIComponent(scopeKey)}` : "";

		return this.httpClient.get<{
			scopeKey: string;
			connections: MemoryTimeline["connections"];
		}>(`${SHADOW_MEMORY_CONNECTIONS_PATH}${query}`);
	}

	getJourney(scopeKey?: string): Promise<LearningJourney> {
		const query = scopeKey ? `?scopeKey=${encodeURIComponent(scopeKey)}` : "";

		return this.httpClient.get<LearningJourney>(
			`${SHADOW_MEMORY_JOURNEY_PATH}${query}`,
		);
	}

	search(request: MemorySearchRequest): Promise<MemorySearchResult> {
		return this.httpClient.post<MemorySearchResult>(
			SHADOW_MEMORY_SEARCH_PATH,
			request,
		);
	}

	reset(scopeKey?: string): Promise<MemoryTimeline> {
		return this.httpClient.post<MemoryTimeline>(
			SHADOW_MEMORY_RESET_PATH,
			scopeKey ? { scopeKey } : {},
		);
	}
}
