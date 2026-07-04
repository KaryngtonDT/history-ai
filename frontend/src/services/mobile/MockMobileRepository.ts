import type { MobileRepository } from "./MobileRepository";
import type {
	MobileConnection,
	MobileConnectionsResponse,
	MobileHealth,
	MobilePreferences,
	MobileProfile,
	MobileServer,
	MobileToday,
	MobileWorkspace,
	RegisterMobileDeviceRequest,
	UpdateMobileConnectionRequest,
	UpdateMobilePreferencesRequest,
} from "./types";

const defaultConnection: MobileConnection = {
	mode: "auto",
	localhostUrl: "http://127.0.0.1:8000",
	lanUrl: "http://192.168.178.21:8000",
	tailscaleUrl: "http://127.0.0.1:8000",
	homeWifiSsids: ["FRITZ!Box 7530 BQ"],
};

const defaultPreferences: MobilePreferences = {
	notificationsEnabled: true,
	notificationFrequency: "daily",
	categories: ["missions", "revisions", "mentor"],
	voiceEnabled: true,
	language: "en",
};

let mockProfile: MobileProfile = {
	id: "mobile-mock-1",
	scopeKey: "default",
	state: "disconnected",
	session: { active: false, session: null },
	device: null,
	connection: defaultConnection,
	preferences: defaultPreferences,
	capabilities: null,
};

const mockToday: MobileToday = {
	scopeKey: "default",
	state: "disconnected",
	missions: [
		{
			id: "mission-1",
			goalId: "goal-docker",
			title: "Docker Compose",
			objective: "Review compose fundamentals",
			durationMinutes: 12,
			status: "active",
			progressPercent: 40,
		},
	],
	missionCount: 1,
	currentMission: null,
	revisions: [
		{
			conceptKey: "docker-compose",
			label: "Docker Compose",
			dueAt: "2026-07-03T09:00:00+00:00",
			intervalDays: 1,
			reason: "spaced_repetition",
		},
	],
	revisionCount: 1,
	agenda: { today: [], todayCount: 0 },
	summary: "1 mission(s) · 1 revision(s)",
};

export class MockMobileRepository implements MobileRepository {
	getProfile(): Promise<MobileProfile> {
		return Promise.resolve(mockProfile);
	}

	getToday(): Promise<MobileToday> {
		return Promise.resolve(mockToday);
	}

	getMissions() {
		return Promise.resolve({
			scopeKey: "default",
			missions: mockToday.missions,
			currentMission: mockToday.currentMission,
		});
	}

	getRevisions() {
		return Promise.resolve({
			scopeKey: "default",
			revisions: mockToday.revisions,
		});
	}

	getServer(): Promise<MobileServer> {
		return Promise.resolve({
			scopeKey: "default",
			status: "ready",
			liveStatus: "live",
			checks: [{ ok: true, label: "Docker production-like compose available" }],
			healthyCount: 8,
			totalChecks: 10,
			available: true,
		});
	}

	getHealth(): Promise<MobileHealth> {
		return Promise.resolve({
			scopeKey: "default",
			connectionMode: mockProfile.connection.mode,
			status: "ready",
			live: "live",
			checks: {},
			liveChecks: {},
		});
	}

	getConnections(): Promise<MobileConnectionsResponse> {
		return Promise.resolve({
			scopeKey: "default",
			connection: mockProfile.connection,
			activeDeviceId: mockProfile.device?.deviceId ?? null,
			state: mockProfile.state,
		});
	}

	registerDevice(
		request: RegisterMobileDeviceRequest,
	): Promise<MobileWorkspace> {
		const device = {
			deviceId: request.deviceId,
			platform: request.platform,
			name: request.name,
			capabilities: {
				voice: true,
				watchCompanion: true,
				notifications: true,
				secondBrain: true,
				...request.capabilities,
			},
			registeredAt: new Date().toISOString(),
			lastSeenAt: new Date().toISOString(),
		};
		mockProfile = {
			...mockProfile,
			state: "connected",
			device,
			capabilities: device.capabilities,
			session: {
				active: true,
				session: {
					id: "mobile-session-1",
					scopeKey: "default",
					deviceId: request.deviceId,
					state: "connected",
					shadowSessionId: null,
					connectedAt: new Date().toISOString(),
					lastActiveAt: new Date().toISOString(),
				},
			},
		};

		return Promise.resolve({
			id: mockProfile.id,
			scopeKey: mockProfile.scopeKey,
			session: mockProfile.session,
			device,
			connection: mockProfile.connection,
			preferences: mockProfile.preferences,
		});
	}

	sync(): Promise<MobileWorkspace> {
		return Promise.resolve({
			id: mockProfile.id,
			scopeKey: mockProfile.scopeKey,
			session: mockProfile.session,
			device: mockProfile.device,
			connection: mockProfile.connection,
			preferences: mockProfile.preferences,
		});
	}

	updatePreferences(
		request: UpdateMobilePreferencesRequest,
	): Promise<MobilePreferences> {
		mockProfile = {
			...mockProfile,
			preferences: { ...mockProfile.preferences, ...request },
		};

		return Promise.resolve(mockProfile.preferences);
	}

	updateConnection(
		request: UpdateMobileConnectionRequest,
	): Promise<MobileConnection> {
		mockProfile = {
			...mockProfile,
			connection: { ...mockProfile.connection, ...request },
		};

		return Promise.resolve(mockProfile.connection);
	}

	registerPushToken(token: string): Promise<{ pushToken: string }> {
		return Promise.resolve({ pushToken: token });
	}
}
