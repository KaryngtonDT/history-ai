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

const defaultPreferences: PresencePreferences = {
	shortcuts: { quickAssist: "Ctrl+Shift+Space" },
	notifications: true,
	voiceEnabled: false,
	proactiveEnabled: false,
	surfaceEnabled: {
		web: true,
		desktop: true,
		browser: false,
		ide: false,
		mobile: false,
	},
	permissions: [
		{ capability: "ask_question", granted: true },
		{ capability: "search_brain", granted: true },
		{ capability: "resume_conversation", granted: true },
		{ capability: "read_selection", granted: false },
		{ capability: "read_page_context", granted: false },
		{ capability: "read_workspace", granted: false },
		{ capability: "proactive_hint", granted: false },
	],
};

let mockSession: PresenceSessionResponse = {
	active: true,
	session: {
		id: "presence-session-web",
		scopeKey: "default",
		surface: "web",
		state: "connected",
		shadowSessionId: "shadow-session-001",
		connectedAt: "2026-06-28T08:00:00+00:00",
		lastActiveAt: "2026-06-28T14:30:00+00:00",
	},
};

let mockPreferences = { ...defaultPreferences };

const mockHistory: PresenceHistory = {
	scopeKey: "default",
	events: [
		{
			id: "evt-1",
			label: "Web session connected",
			surface: "web",
			reason: "user_invoked",
			detail: "Opened Shadow settings presence tab",
			recordedAt: "2026-06-28T14:30:00+00:00",
			permissionsUsed: ["ask_question", "search_brain"],
		},
		{
			id: "evt-2",
			label: "Second Brain search",
			surface: "web",
			reason: "user_invoked",
			detail: "Searched concept docker",
			recordedAt: "2026-06-28T10:15:00+00:00",
			permissionsUsed: ["search_brain"],
		},
		{
			id: "evt-3",
			label: "Mission reminder",
			surface: "desktop",
			reason: "scheduled_mission",
			detail: "Executive planned revision for docker_networking",
			recordedAt: "2026-06-27T22:00:00+00:00",
			permissionsUsed: ["proactive_hint"],
		},
	],
};

const mockContext: PresenceContext = {
	scopeKey: "default",
	surface: "web",
	identityLabel: "Shadow — Mentor mode",
	conceptCount: 42,
	activeMissionTitle: "Strengthen Docker networking",
	executiveHint: "Revision due for docker_networking before Kubernetes labs",
	conversationSessionId: "shadow-session-001",
	explainability: {
		reason: "user_invoked",
		detail: "Presence context built from Second Brain and Executive",
	},
};

const mockExplain: PresenceExplain = {
	reason: "user_invoked",
	detail: "You opened Shadow Presence settings",
	surface: "web",
	permissionsUsed: ["ask_question", "search_brain"],
};

export class MockPresenceRepository implements PresenceRepository {
	getSession(): Promise<PresenceSessionResponse> {
		return Promise.resolve(mockSession);
	}

	connect(request: ConnectPresenceRequest): Promise<PresenceWorkspace> {
		const now = new Date().toISOString();
		mockSession = {
			active: true,
			session: {
				id: `presence-${request.surface}`,
				scopeKey: request.scopeKey ?? "default",
				surface: request.surface,
				state: "connected",
				shadowSessionId: request.shadowSessionId ?? "shadow-session-001",
				connectedAt: now,
				lastActiveAt: now,
			},
		};

		return Promise.resolve(this.workspace());
	}

	disconnect(): Promise<PresenceWorkspace> {
		mockSession = { active: false, session: null };

		return Promise.resolve(this.workspace());
	}

	getContext(surface?: string): Promise<PresenceContext> {
		return Promise.resolve({
			...mockContext,
			surface: (surface as PresenceContext["surface"]) ?? mockContext.surface,
		});
	}

	updatePreferences(
		request: UpdatePresencePreferencesRequest,
	): Promise<{ scopeKey: string; preferences: PresencePreferences }> {
		mockPreferences = {
			...mockPreferences,
			...request,
			shortcuts: { ...mockPreferences.shortcuts, ...request.shortcuts },
			surfaceEnabled: {
				...mockPreferences.surfaceEnabled,
				...request.surfaceEnabled,
			},
		};

		return Promise.resolve({
			scopeKey: request.scopeKey ?? "default",
			preferences: mockPreferences,
		});
	}

	getHistory(limit?: number): Promise<PresenceHistory> {
		const events =
			typeof limit === "number"
				? mockHistory.events.slice(0, limit)
				: mockHistory.events;

		return Promise.resolve({ ...mockHistory, events });
	}

	getExplain(): Promise<PresenceExplain> {
		return Promise.resolve(mockExplain);
	}

	private workspace(): PresenceWorkspace {
		return {
			id: "presence-workspace-default",
			scopeKey: "default",
			preferences: mockPreferences,
			session: mockSession,
		};
	}
}
