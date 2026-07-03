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

export interface PresenceRepository {
	getSession(scopeKey?: string): Promise<PresenceSessionResponse>;

	connect(request: ConnectPresenceRequest): Promise<PresenceWorkspace>;

	disconnect(scopeKey?: string): Promise<PresenceWorkspace>;

	getContext(surface?: string, scopeKey?: string): Promise<PresenceContext>;

	updatePreferences(
		request: UpdatePresencePreferencesRequest,
	): Promise<{ scopeKey: string; preferences: PresencePreferences }>;

	getHistory(limit?: number, scopeKey?: string): Promise<PresenceHistory>;

	getExplain(scopeKey?: string): Promise<PresenceExplain>;
}
