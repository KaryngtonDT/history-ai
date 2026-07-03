import {
	SHADOW_PRESENCE_CONNECT_PATH,
	SHADOW_PRESENCE_CONTEXT_PATH,
	SHADOW_PRESENCE_DISCONNECT_PATH,
	SHADOW_PRESENCE_EXPLAIN_PATH,
	SHADOW_PRESENCE_HISTORY_PATH,
	SHADOW_PRESENCE_PREFERENCES_PATH,
	SHADOW_PRESENCE_SESSION_PATH,
} from "@/config/api";
import type { HttpClient } from "@/services/http/HttpClient";
import type { PresenceRepository } from "./PresenceRepository";
import type {
	ConnectPresenceRequest,
	PresenceContext,
	PresenceExplain,
	PresenceHistory,
	PresencePreferences,
	PresenceSessionResponse,
	PresenceWorkspace,
	UpdatePresencePreferencesRequest,
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

export class HttpPresenceRepository implements PresenceRepository {
	private readonly httpClient: HttpClient;

	constructor(httpClient: HttpClient) {
		this.httpClient = httpClient;
	}

	getSession(scopeKey?: string): Promise<PresenceSessionResponse> {
		const query = scopeQuery(scopeKey);

		return this.httpClient.get<PresenceSessionResponse>(
			appendQuery(SHADOW_PRESENCE_SESSION_PATH, query ? [query] : []),
		);
	}

	connect(request: ConnectPresenceRequest): Promise<PresenceWorkspace> {
		return this.httpClient.post<PresenceWorkspace>(
			SHADOW_PRESENCE_CONNECT_PATH,
			request,
		);
	}

	disconnect(scopeKey?: string): Promise<PresenceWorkspace> {
		return this.httpClient.post<PresenceWorkspace>(
			SHADOW_PRESENCE_DISCONNECT_PATH,
			scopeKey ? { scopeKey } : {},
		);
	}

	getContext(surface?: string, scopeKey?: string): Promise<PresenceContext> {
		const params = [scopeQuery(scopeKey)];
		if (surface) {
			params.push(`surface=${encodeURIComponent(surface)}`);
		}

		return this.httpClient.get<PresenceContext>(
			appendQuery(SHADOW_PRESENCE_CONTEXT_PATH, params),
		);
	}

	updatePreferences(
		request: UpdatePresencePreferencesRequest,
	): Promise<{ scopeKey: string; preferences: PresencePreferences }> {
		return this.httpClient.put<{
			scopeKey: string;
			preferences: PresencePreferences;
		}>(SHADOW_PRESENCE_PREFERENCES_PATH, request);
	}

	getHistory(limit?: number, scopeKey?: string): Promise<PresenceHistory> {
		const params = [scopeQuery(scopeKey)];
		if (typeof limit === "number") {
			params.push(`limit=${limit}`);
		}

		return this.httpClient.get<PresenceHistory>(
			appendQuery(SHADOW_PRESENCE_HISTORY_PATH, params),
		);
	}

	getExplain(scopeKey?: string): Promise<PresenceExplain> {
		const query = scopeQuery(scopeKey);

		return this.httpClient.get<PresenceExplain>(
			appendQuery(SHADOW_PRESENCE_EXPLAIN_PATH, query ? [query] : []),
		);
	}
}
