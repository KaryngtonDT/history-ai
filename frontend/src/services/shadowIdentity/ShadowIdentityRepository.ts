import type {
	ShadowConfigurationResult,
	ShadowIdentityProfile,
	UpdateShadowIdentityPreferencesRequest,
} from "./types";

export interface ShadowIdentityRepository {
	getProfile(scopeKey?: string): Promise<ShadowIdentityProfile>;
	updatePreferences(
		request: UpdateShadowIdentityPreferencesRequest,
	): Promise<ShadowIdentityProfile>;
	reset(scopeKey?: string): Promise<ShadowIdentityProfile>;
	configure(
		utterance: string,
		confirmed?: boolean,
		scopeKey?: string,
	): Promise<ShadowConfigurationResult>;
}
