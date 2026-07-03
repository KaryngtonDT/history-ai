import type {
	MobileConnectionsResponse,
	MobileHealth,
	MobileProfile,
	MobileServer,
	MobileToday,
	MobileWorkspace,
	MobileConnection,
	MobilePreferences,
	RegisterMobileDeviceRequest,
	UpdateMobileConnectionRequest,
	UpdateMobilePreferencesRequest,
} from "./types";

export interface MobileRepository {
	getProfile(scopeKey?: string): Promise<MobileProfile>;

	getToday(scopeKey?: string): Promise<MobileToday>;

	getMissions(scopeKey?: string): Promise<{
		scopeKey: string;
		missions: MobileToday["missions"];
		currentMission: MobileToday["currentMission"];
	}>;

	getRevisions(scopeKey?: string): Promise<{
		scopeKey: string;
		revisions: MobileToday["revisions"];
	}>;

	getServer(scopeKey?: string): Promise<MobileServer>;

	getHealth(scopeKey?: string): Promise<MobileHealth>;

	getConnections(scopeKey?: string): Promise<MobileConnectionsResponse>;

	registerDevice(request: RegisterMobileDeviceRequest): Promise<MobileWorkspace>;

	sync(scopeKey?: string): Promise<MobileWorkspace>;

	updatePreferences(
		request: UpdateMobilePreferencesRequest,
	): Promise<MobilePreferences>;

	updateConnection(
		request: UpdateMobileConnectionRequest,
	): Promise<MobileConnection>;

	registerPushToken(
		token: string,
		scopeKey?: string,
	): Promise<{ pushToken: string }>;
}
