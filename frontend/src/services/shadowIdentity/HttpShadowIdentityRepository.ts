import {
	SHADOW_IDENTITY_CONFIGURE_PATH,
	SHADOW_IDENTITY_PREFERENCES_PATH,
	SHADOW_IDENTITY_PROFILE_PATH,
	SHADOW_IDENTITY_RESET_PATH,
} from "@/config/api";
import type { HttpClient } from "@/services/http/HttpClient";
import type { ShadowIdentityRepository } from "./ShadowIdentityRepository";
import type {
	ShadowConfigurationResult,
	ShadowIdentityProfile,
	UpdateShadowIdentityPreferencesRequest,
} from "./types";

export class HttpShadowIdentityRepository implements ShadowIdentityRepository {
	private readonly httpClient: HttpClient;

	constructor(httpClient: HttpClient) {
		this.httpClient = httpClient;
	}

	getProfile(scopeKey?: string): Promise<ShadowIdentityProfile> {
		const params = scopeKey
			? new URLSearchParams({ scopeKey })
			: new URLSearchParams();
		const query = params.toString();
		const path = query
			? `${SHADOW_IDENTITY_PROFILE_PATH}?${query}`
			: SHADOW_IDENTITY_PROFILE_PATH;

		return this.httpClient.get<ShadowIdentityProfile>(path);
	}

	updatePreferences(
		request: UpdateShadowIdentityPreferencesRequest,
	): Promise<ShadowIdentityProfile> {
		return this.httpClient.put<ShadowIdentityProfile>(
			SHADOW_IDENTITY_PREFERENCES_PATH,
			request,
		);
	}

	reset(scopeKey?: string): Promise<ShadowIdentityProfile> {
		return this.httpClient.post<ShadowIdentityProfile>(
			SHADOW_IDENTITY_RESET_PATH,
			{ scopeKey },
		);
	}

	configure(
		utterance: string,
		confirmed = false,
		scopeKey?: string,
	): Promise<ShadowConfigurationResult> {
		return this.httpClient.post<ShadowConfigurationResult>(
			SHADOW_IDENTITY_CONFIGURE_PATH,
			{ utterance, confirmed, scopeKey },
		);
	}
}
