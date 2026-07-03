import {
	SHADOW_MOBILE_CONNECTION_PATH,
	SHADOW_MOBILE_CONNECTIONS_PATH,
	SHADOW_MOBILE_DEVICE_PATH,
	SHADOW_MOBILE_HEALTH_PATH,
	SHADOW_MOBILE_MISSIONS_PATH,
	SHADOW_MOBILE_PREFERENCES_PATH,
	SHADOW_MOBILE_PROFILE_PATH,
	SHADOW_MOBILE_PUSH_TOKEN_PATH,
	SHADOW_MOBILE_REVISIONS_PATH,
	SHADOW_MOBILE_SERVER_PATH,
	SHADOW_MOBILE_SYNC_PATH,
	SHADOW_MOBILE_TODAY_PATH,
} from "@/config/api";
import type { HttpClient } from "@/services/http/HttpClient";
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

export class HttpMobileRepository implements MobileRepository {
	private readonly httpClient: HttpClient;

	constructor(httpClient: HttpClient) {
		this.httpClient = httpClient;
	}

	getProfile(scopeKey?: string): Promise<MobileProfile> {
		return this.httpClient.get<MobileProfile>(
			appendQuery(
				SHADOW_MOBILE_PROFILE_PATH,
				scopeQuery(scopeKey) ? [scopeQuery(scopeKey)] : [],
			),
		);
	}

	getToday(scopeKey?: string): Promise<MobileToday> {
		return this.httpClient.get<MobileToday>(
			appendQuery(
				SHADOW_MOBILE_TODAY_PATH,
				scopeQuery(scopeKey) ? [scopeQuery(scopeKey)] : [],
			),
		);
	}

	getMissions(scopeKey?: string): Promise<{
		scopeKey: string;
		missions: MobileToday["missions"];
		currentMission: MobileToday["currentMission"];
	}> {
		return this.httpClient.get(
			appendQuery(
				SHADOW_MOBILE_MISSIONS_PATH,
				scopeQuery(scopeKey) ? [scopeQuery(scopeKey)] : [],
			),
		);
	}

	getRevisions(scopeKey?: string): Promise<{
		scopeKey: string;
		revisions: MobileToday["revisions"];
	}> {
		return this.httpClient.get(
			appendQuery(
				SHADOW_MOBILE_REVISIONS_PATH,
				scopeQuery(scopeKey) ? [scopeQuery(scopeKey)] : [],
			),
		);
	}

	getServer(scopeKey?: string): Promise<MobileServer> {
		return this.httpClient.get<MobileServer>(
			appendQuery(
				SHADOW_MOBILE_SERVER_PATH,
				scopeQuery(scopeKey) ? [scopeQuery(scopeKey)] : [],
			),
		);
	}

	getHealth(scopeKey?: string): Promise<MobileHealth> {
		return this.httpClient.get<MobileHealth>(
			appendQuery(
				SHADOW_MOBILE_HEALTH_PATH,
				scopeQuery(scopeKey) ? [scopeQuery(scopeKey)] : [],
			),
		);
	}

	getConnections(scopeKey?: string): Promise<MobileConnectionsResponse> {
		return this.httpClient.get<MobileConnectionsResponse>(
			appendQuery(
				SHADOW_MOBILE_CONNECTIONS_PATH,
				scopeQuery(scopeKey) ? [scopeQuery(scopeKey)] : [],
			),
		);
	}

	registerDevice(
		request: RegisterMobileDeviceRequest,
	): Promise<MobileWorkspace> {
		return this.httpClient.post<MobileWorkspace>(
			SHADOW_MOBILE_DEVICE_PATH,
			request,
		);
	}

	sync(scopeKey?: string): Promise<MobileWorkspace> {
		return this.httpClient.post<MobileWorkspace>(
			SHADOW_MOBILE_SYNC_PATH,
			scopeKey ? { scopeKey } : {},
		);
	}

	updatePreferences(
		request: UpdateMobilePreferencesRequest,
	): Promise<MobilePreferences> {
		return this.httpClient
			.put<{ scopeKey: string; preferences: MobilePreferences }>(
				SHADOW_MOBILE_PREFERENCES_PATH,
				request,
			)
			.then((response) => response.preferences);
	}

	updateConnection(
		request: UpdateMobileConnectionRequest,
	): Promise<MobileConnection> {
		return this.httpClient
			.put<{ scopeKey: string; connection: MobileConnection }>(
				SHADOW_MOBILE_CONNECTION_PATH,
				request,
			)
			.then((response) => response.connection);
	}

	registerPushToken(
		token: string,
		scopeKey?: string,
	): Promise<{ pushToken: string }> {
		return this.httpClient.post<{ pushToken: string }>(
			SHADOW_MOBILE_PUSH_TOKEN_PATH,
			scopeKey ? { scopeKey, token } : { token },
		);
	}
}
