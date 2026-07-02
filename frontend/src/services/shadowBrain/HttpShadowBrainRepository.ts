import {
	SHADOW_BRAIN_BOOKMARK_PATH,
	SHADOW_BRAIN_CONCEPTS_PATH,
	SHADOW_BRAIN_DIFF_PATH,
	SHADOW_BRAIN_NOTE_PATH,
	SHADOW_BRAIN_PATH,
	SHADOW_BRAIN_REBUILD_PATH,
	SHADOW_BRAIN_SEARCH_PATH,
	SHADOW_BRAIN_TIMELINE_PATH,
	shadowBrainBookmarkPath,
	shadowBrainConceptPath,
} from "@/config/api";
import type { HttpClient } from "@/services/http/HttpClient";
import type { ShadowBrainRepository } from "./ShadowBrainRepository";
import type {
	AddBookmarkRequest,
	AddNoteRequest,
	BrainDashboard,
	ConceptDetail,
	ConceptTreeResponse,
	KnowledgeBookmark,
	KnowledgeDiff,
	KnowledgeNote,
	KnowledgeSearchResponse,
	RebuildWorkspaceRequest,
	TimelineResponse,
} from "./types";

function scopeQuery(scopeKey?: string): string {
	return scopeKey ? `scopeKey=${encodeURIComponent(scopeKey)}` : "";
}

function appendQuery(base: string, params: string[]): string {
	const filtered = params.filter((param) => param.length > 0);
	if (filtered.length === 0) {
		return base;
	}

	const separator = base.includes("?") ? "&" : "?";

	return `${base}${separator}${filtered.join("&")}`;
}

export class HttpShadowBrainRepository implements ShadowBrainRepository {
	private readonly httpClient: HttpClient;

	constructor(httpClient: HttpClient) {
		this.httpClient = httpClient;
	}

	getDashboard(scopeKey?: string): Promise<BrainDashboard> {
		const query = scopeQuery(scopeKey);

		return this.httpClient.get<BrainDashboard>(
			appendQuery(SHADOW_BRAIN_PATH, query ? [query] : []),
		);
	}

	getConceptTree(scopeKey?: string): Promise<ConceptTreeResponse> {
		const query = scopeQuery(scopeKey);

		return this.httpClient.get<ConceptTreeResponse>(
			appendQuery(SHADOW_BRAIN_CONCEPTS_PATH, query ? [query] : []),
		);
	}

	getConcept(id: string, scopeKey?: string): Promise<ConceptDetail> {
		const query = scopeQuery(scopeKey);

		return this.httpClient.get<ConceptDetail>(
			appendQuery(shadowBrainConceptPath(id), query ? [query] : []),
		);
	}

	search(query: string, scopeKey?: string): Promise<KnowledgeSearchResponse> {
		const params = [`q=${encodeURIComponent(query)}`];
		const scope = scopeQuery(scopeKey);

		if (scope) {
			params.push(scope);
		}

		return this.httpClient.get<KnowledgeSearchResponse>(
			appendQuery(SHADOW_BRAIN_SEARCH_PATH, params),
		);
	}

	getTimeline(
		from?: string,
		to?: string,
		scopeKey?: string,
	): Promise<TimelineResponse> {
		const params: string[] = [];
		const scope = scopeQuery(scopeKey);

		if (from) {
			params.push(`from=${encodeURIComponent(from)}`);
		}

		if (to) {
			params.push(`to=${encodeURIComponent(to)}`);
		}

		if (scope) {
			params.push(scope);
		}

		return this.httpClient.get<TimelineResponse>(
			appendQuery(SHADOW_BRAIN_TIMELINE_PATH, params),
		);
	}

	getDiff(
		resourceType: string,
		resourceId: string,
		scopeKey?: string,
	): Promise<KnowledgeDiff> {
		const params = [
			`resourceType=${encodeURIComponent(resourceType)}`,
			`resourceId=${encodeURIComponent(resourceId)}`,
		];
		const scope = scopeQuery(scopeKey);

		if (scope) {
			params.push(scope);
		}

		return this.httpClient.get<KnowledgeDiff>(
			appendQuery(SHADOW_BRAIN_DIFF_PATH, params),
		);
	}

	addBookmark(request: AddBookmarkRequest): Promise<KnowledgeBookmark> {
		return this.httpClient.post<KnowledgeBookmark>(
			SHADOW_BRAIN_BOOKMARK_PATH,
			request,
		);
	}

	addNote(request: AddNoteRequest): Promise<KnowledgeNote> {
		return this.httpClient.post<KnowledgeNote>(SHADOW_BRAIN_NOTE_PATH, request);
	}

	removeBookmark(id: string, scopeKey?: string): Promise<void> {
		const query = scopeQuery(scopeKey);

		return this.httpClient.delete(
			appendQuery(shadowBrainBookmarkPath(id), query ? [query] : []),
		);
	}

	rebuild(request: RebuildWorkspaceRequest = {}): Promise<BrainDashboard> {
		return this.httpClient.post<BrainDashboard>(
			SHADOW_BRAIN_REBUILD_PATH,
			request,
		);
	}
}
