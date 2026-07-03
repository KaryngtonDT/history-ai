export type PresenceSurface = "web" | "desktop" | "browser" | "ide" | "mobile";

export type PresenceState = "connected" | "idle" | "disconnected";

export type PresenceCapability =
	| "ask_question"
	| "search_brain"
	| "resume_conversation"
	| "read_selection"
	| "read_page_context"
	| "read_workspace"
	| "proactive_hint";

export interface PresencePermission {
	capability: PresenceCapability;
	granted: boolean;
}

export interface PresenceSession {
	id: string;
	scopeKey: string;
	surface: PresenceSurface;
	state: PresenceState;
	shadowSessionId: string | null;
	connectedAt: string;
	lastActiveAt: string;
}

export interface PresenceSessionResponse {
	active: boolean;
	session: PresenceSession | null;
}

export interface PresenceContext {
	scopeKey: string;
	surface: PresenceSurface;
	identityLabel: string;
	conceptCount: number;
	activeMissionTitle: string | null;
	executiveHint: string | null;
	conversationSessionId: string | null;
	explainability: Record<string, string>;
}

export interface PresencePreferences {
	shortcuts: Record<string, string>;
	notifications: boolean;
	voiceEnabled: boolean;
	proactiveEnabled: boolean;
	surfaceEnabled: Record<PresenceSurface, boolean>;
	permissions: PresencePermission[];
}

export interface PresenceEvent {
	id: string;
	label: string;
	surface: PresenceSurface;
	reason: string;
	detail: string;
	recordedAt: string;
	permissionsUsed: string[];
}

export interface PresenceHistory {
	scopeKey: string;
	events: PresenceEvent[];
}

export interface PresenceExplain {
	reason: string;
	detail: string;
	surface: PresenceSurface | null;
	permissionsUsed: string[];
}

export interface PresenceWorkspace {
	id: string;
	scopeKey: string;
	preferences: PresencePreferences;
	session: PresenceSessionResponse;
}

export interface ConnectPresenceRequest {
	scopeKey?: string;
	surface: PresenceSurface;
	shadowSessionId?: string | null;
}

export interface UpdatePresencePreferencesRequest {
	scopeKey?: string;
	shortcuts?: Record<string, string>;
	notifications?: boolean;
	voiceEnabled?: boolean;
	proactiveEnabled?: boolean;
	surfaceEnabled?: Partial<Record<PresenceSurface, boolean>>;
}
