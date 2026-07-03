import type { BrowserRepository } from "./BrowserRepository";
import type {
	BrowserExplain,
	BrowserHistory,
	BrowserPermissionsResponse,
	BrowserPlatformResult,
	BrowserSessionResponse,
	BrowserSitePolicy,
	BrowserWorkspace,
	ConnectBrowserRequest,
	DetectBrowserPlatformRequest,
	UpdateBrowserContextRequest,
	UpdateBrowserContextResponse,
	UpdateBrowserPermissionsRequest,
} from "./types";

const defaultPermissions: BrowserSitePolicy["permissions"] = [
	{ permission: "ask_question", granted: true },
	{ permission: "search_brain", granted: true },
	{ permission: "resume_conversation", granted: true },
	{ permission: "read_selection", granted: false },
	{ permission: "read_page_context", granted: false },
	{ permission: "detect_platform", granted: true },
	{ permission: "capture_url", granted: true },
	{ permission: "proactive_hint", granted: false },
];

let mockSession: BrowserSessionResponse = {
	active: false,
	session: null,
};

const mockSitePolicies: BrowserSitePolicy[] = [
	{
		host: "youtube.com",
		allowed: true,
		permissions: defaultPermissions.map((permission) =>
			permission.permission === "read_page_context" ||
			permission.permission === "read_selection"
				? { ...permission, granted: true }
				: permission,
		),
	},
];

const mockHistory: BrowserHistory = {
	scopeKey: "default",
	activities: [
		{
			id: "browser-act-1",
			label: "Browser companion connected",
			platform: "unknown",
			reason: "user_invoked",
			detail: "Opened Shadow browser settings",
			recordedAt: "2026-07-01T10:00:00+00:00",
			permissionsUsed: ["ask_question", "search_brain"],
			url: "",
		},
		{
			id: "browser-act-2",
			label: "Platform detected",
			platform: "youtube",
			reason: "platform_detection",
			detail: "Detected youtube for youtube.com",
			recordedAt: "2026-07-01T10:05:00+00:00",
			permissionsUsed: ["detect_platform"],
			url: "https://www.youtube.com/watch?v=demo",
		},
		{
			id: "browser-act-3",
			label: "Reading selection captured",
			platform: "wikipedia",
			reason: "context_update",
			detail: "Shadow read selected text on Wikipedia",
			recordedAt: "2026-06-30T18:30:00+00:00",
			permissionsUsed: ["read_selection", "read_page_context"],
			url: "https://en.wikipedia.org/wiki/Docker_(software)",
		},
	],
};

const mockExplain: BrowserExplain = {
	reason: "platform_detection",
	detail: "Detected youtube for youtube.com",
	platform: "youtube",
	permissionsUsed: ["detect_platform"],
	url: "https://www.youtube.com/watch?v=demo",
	humanReadable: "Shadow detected platform youtube for the current page.",
};

let mockContext: UpdateBrowserContextResponse["context"] = {
	available: false,
	context: null,
};

function detectPlatformFromUrl(url: string): BrowserPlatformResult {
	const normalized = url.toLowerCase();

	if (normalized.includes("youtube.com") || normalized.includes("youtu.be")) {
		return { url, platform: "youtube", host: "youtube.com" };
	}

	if (normalized.includes("wikipedia.org")) {
		return { url, platform: "wikipedia", host: "wikipedia.org" };
	}

	if (normalized.includes("developer.mozilla.org")) {
		return { url, platform: "mdn", host: "developer.mozilla.org" };
	}

	if (normalized.includes("github.com")) {
		return { url, platform: "github", host: "github.com" };
	}

	return { url, platform: "unknown", host: "unknown" };
}

export class MockBrowserRepository implements BrowserRepository {
	getSession(): Promise<BrowserSessionResponse> {
		return Promise.resolve(mockSession);
	}

	connect(request: ConnectBrowserRequest): Promise<BrowserWorkspace> {
		const now = new Date().toISOString();
		mockSession = {
			active: true,
			session: {
				id: "browser-session-default",
				scopeKey: request.scopeKey ?? "default",
				state: "connected",
				shadowSessionId: request.shadowSessionId ?? "shadow-session-001",
				activeTab: null,
				connectedAt: now,
				lastActiveAt: now,
			},
		};

		return Promise.resolve(this.workspace());
	}

	disconnect(): Promise<BrowserWorkspace> {
		mockSession = { active: false, session: null };

		return Promise.resolve(this.workspace());
	}

	updateContext(
		request: UpdateBrowserContextRequest,
	): Promise<UpdateBrowserContextResponse> {
		const platform = detectPlatformFromUrl(request.url).platform;
		mockContext = {
			available: true,
			context: {
				scopeKey: request.scopeKey ?? "default",
				url: request.url,
				title: request.title,
				tabId: request.tabId,
				platform,
				selection: request.selection ?? null,
				shadowSessionId: "shadow-session-001",
				conversationSessionId: "shadow-session-001",
			},
		};

		return Promise.resolve({
			scopeKey: request.scopeKey ?? "default",
			session: mockSession,
			context: mockContext,
		});
	}

	detectPlatform(
		request: DetectBrowserPlatformRequest,
	): Promise<BrowserPlatformResult> {
		return Promise.resolve(detectPlatformFromUrl(request.url));
	}

	getPermissions(): Promise<BrowserPermissionsResponse> {
		return Promise.resolve({
			scopeKey: "default",
			sitePolicies: mockSitePolicies,
			defaults: {
				host: "default",
				allowed: true,
				permissions: defaultPermissions,
			},
		});
	}

	updatePermissions(
		request: UpdateBrowserPermissionsRequest,
	): Promise<BrowserPermissionsResponse> {
		for (const policy of request.sitePolicies) {
			const index = mockSitePolicies.findIndex(
				(existing) => existing.host === policy.host,
			);

			if (index >= 0) {
				mockSitePolicies[index] = policy;
			} else {
				mockSitePolicies.push(policy);
			}
		}

		return this.getPermissions();
	}

	getHistory(limit?: number): Promise<BrowserHistory> {
		const activities =
			typeof limit === "number"
				? mockHistory.activities.slice(0, limit)
				: mockHistory.activities;

		return Promise.resolve({ ...mockHistory, activities });
	}

	getExplain(): Promise<BrowserExplain> {
		return Promise.resolve(mockExplain);
	}

	private workspace(): BrowserWorkspace {
		return {
			id: "browser-workspace-default",
			scopeKey: "default",
			session: mockSession,
			context: mockContext,
		};
	}
}
