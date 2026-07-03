export type BrowserState = "connected" | "idle" | "disconnected";

export type BrowserPlatform =
	| "youtube"
	| "wikipedia"
	| "mdn"
	| "symfony_docs"
	| "php_docs"
	| "github"
	| "gitlab"
	| "stackoverflow"
	| "reddit"
	| "pdf_viewer"
	| "unknown";

export type BrowserPermission =
	| "ask_question"
	| "search_brain"
	| "resume_conversation"
	| "read_selection"
	| "read_page_context"
	| "detect_platform"
	| "capture_url"
	| "proactive_hint";

export interface BrowserPermissionGrant {
	permission: BrowserPermission;
	granted: boolean;
}

export interface BrowserTab {
	tabId: string;
	url: string;
	title: string;
	platform: BrowserPlatform;
	selection: string | null;
}

export interface BrowserSession {
	id: string;
	scopeKey: string;
	state: BrowserState;
	shadowSessionId: string | null;
	activeTab: BrowserTab | null;
	connectedAt: string;
	lastActiveAt: string;
}

export interface BrowserSessionResponse {
	active: boolean;
	session: BrowserSession | null;
}

export interface BrowserContext {
	scopeKey: string;
	url: string;
	title: string;
	tabId: string;
	platform: BrowserPlatform;
	selection: string | null;
	shadowSessionId: string | null;
	conversationSessionId: string | null;
}

export interface BrowserContextResponse {
	available: boolean;
	context: BrowserContext | null;
}

export interface BrowserSitePolicy {
	host: string;
	allowed: boolean;
	permissions: BrowserPermissionGrant[];
}

export interface BrowserPermissionsResponse {
	scopeKey: string;
	sitePolicies: BrowserSitePolicy[];
	defaults: BrowserSitePolicy;
}

export interface BrowserActivity {
	id: string;
	label: string;
	platform: BrowserPlatform;
	reason: string;
	detail: string;
	recordedAt: string;
	permissionsUsed: string[];
	url: string;
}

export interface BrowserHistory {
	scopeKey: string;
	activities: BrowserActivity[];
}

export interface BrowserExplain {
	reason: string;
	detail: string;
	platform: BrowserPlatform | null;
	permissionsUsed: string[];
	url?: string;
	humanReadable?: string;
}

export interface BrowserWorkspace {
	id: string;
	scopeKey: string;
	session: BrowserSessionResponse;
	context: BrowserContextResponse;
}

export interface ConnectBrowserRequest {
	scopeKey?: string;
	shadowSessionId?: string | null;
}

export interface UpdateBrowserContextRequest {
	scopeKey?: string;
	url: string;
	title: string;
	tabId: string;
	selection?: string;
}

export interface UpdateBrowserContextResponse {
	scopeKey: string;
	session: BrowserSessionResponse;
	context: BrowserContextResponse;
}

export interface DetectBrowserPlatformRequest {
	scopeKey?: string;
	url: string;
}

export interface BrowserPlatformResult {
	url: string;
	platform: BrowserPlatform;
	host: string;
}

export interface UpdateBrowserPermissionsRequest {
	scopeKey?: string;
	sitePolicies: Array<{
		host: string;
		allowed: boolean;
		permissions: BrowserPermissionGrant[];
	}>;
}
