export type MobileConnectionMode =
	| "localhost"
	| "lan"
	| "tailscale"
	| "auto"
	| "cloud";

export interface MobileCapabilities {
	voice: boolean;
	watchCompanion: boolean;
	notifications: boolean;
	secondBrain: boolean;
}

export interface MobileConnection {
	mode: MobileConnectionMode;
	localhostUrl: string;
	lanUrl: string;
	tailscaleUrl: string;
	homeWifiSsids: string[];
}

export interface MobilePreferences {
	notificationsEnabled: boolean;
	notificationFrequency: "daily" | "weekly";
	categories: string[];
	voiceEnabled: boolean;
	language: string;
}

export interface MobileDevice {
	deviceId: string;
	platform: string;
	name: string;
	capabilities: MobileCapabilities;
	registeredAt: string;
	lastSeenAt: string;
}

export interface MobileSessionInfo {
	id: string;
	scopeKey: string;
	deviceId: string;
	state: string;
	shadowSessionId: string | null;
	connectedAt: string;
	lastActiveAt: string;
}

export interface MobileSessionResponse {
	active: boolean;
	session: MobileSessionInfo | null;
}

export interface MobileProfile {
	id: string;
	scopeKey: string;
	state: string;
	session: MobileSessionResponse;
	device: MobileDevice | null;
	connection: MobileConnection;
	preferences: MobilePreferences;
	capabilities: MobileCapabilities | null;
}

export interface MobileMission {
	id: string;
	goalId: string;
	title: string;
	objective: string;
	durationMinutes: number;
	status: string;
	progressPercent: number;
}

export interface MobileRevision {
	conceptKey: string;
	label: string;
	dueAt: string;
	intervalDays: number;
	reason: string;
}

export interface MobileToday {
	scopeKey: string;
	state: string;
	missions: MobileMission[];
	missionCount: number;
	currentMission: MobileMission | null;
	revisions: MobileRevision[];
	revisionCount: number;
	agenda: {
		today: Array<{
			id: string;
			type: string;
			label: string;
			detail: string;
			order: number;
			scheduledAt: string | null;
		}>;
		todayCount: number;
	};
	summary: string;
}

export interface MobileHealth {
	scopeKey: string;
	connectionMode: MobileConnectionMode;
	status: string;
	live: string;
	checks: Record<string, unknown>;
	liveChecks: Record<string, unknown>;
}

export interface MobileServer {
	scopeKey: string;
	status: string;
	liveStatus: string;
	checks: Array<{ ok?: boolean; label?: string }>;
	healthyCount: number;
	totalChecks: number;
	available: boolean;
}

export interface MobileConnectionsResponse {
	scopeKey: string;
	connection: MobileConnection;
	activeDeviceId: string | null;
	state: string;
}

export interface RegisterMobileDeviceRequest {
	scopeKey?: string;
	deviceId: string;
	platform: string;
	name: string;
	capabilities?: Partial<MobileCapabilities>;
	shadowSessionId?: string;
}

export interface UpdateMobileConnectionRequest {
	scopeKey?: string;
	mode?: MobileConnectionMode;
	localhostUrl?: string;
	lanUrl?: string;
	tailscaleUrl?: string;
	homeWifiSsids?: string[];
}

export interface UpdateMobilePreferencesRequest {
	scopeKey?: string;
	notificationsEnabled?: boolean;
	notificationFrequency?: "daily" | "weekly";
	categories?: string[];
	voiceEnabled?: boolean;
	language?: string;
}

export interface MobileWorkspace {
	id: string;
	scopeKey: string;
	session: MobileSessionResponse;
	device: MobileDevice | null;
	connection: MobileConnection;
	preferences: MobilePreferences;
}
