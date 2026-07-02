import type { ShadowIdentityRepository } from "./ShadowIdentityRepository";
import type {
	ShadowConfigurationResult,
	ShadowIdentityProfile,
	UpdateShadowIdentityPreferencesRequest,
} from "./types";

const API_BASE =
	import.meta.env.VITE_API_BASE_URL?.replace(/\/$/, "") ??
	"http://localhost:8000";

export class HttpShadowIdentityRepository implements ShadowIdentityRepository {
	private readonly baseUrl: string;

	constructor(baseUrl: string = API_BASE) {
		this.baseUrl = baseUrl;
	}

	getProfile(scopeKey?: string): Promise<ShadowIdentityProfile> {
		const query = scopeKey ? `?scopeKey=${encodeURIComponent(scopeKey)}` : "";
		return this.request<ShadowIdentityProfile>(
			`/api/shadow/identity/profile${query}`,
		);
	}

	updatePreferences(
		request: UpdateShadowIdentityPreferencesRequest,
	): Promise<ShadowIdentityProfile> {
		return this.request<ShadowIdentityProfile>(
			"/api/shadow/identity/preferences",
			{
				method: "PUT",
				body: JSON.stringify(request),
			},
		);
	}

	reset(scopeKey?: string): Promise<ShadowIdentityProfile> {
		return this.request<ShadowIdentityProfile>("/api/shadow/identity/reset", {
			method: "POST",
			body: JSON.stringify({ scopeKey }),
		});
	}

	configure(
		utterance: string,
		confirmed = false,
		scopeKey?: string,
	): Promise<ShadowConfigurationResult> {
		return this.request<ShadowConfigurationResult>(
			"/api/shadow/identity/configure",
			{
				method: "POST",
				body: JSON.stringify({ utterance, confirmed, scopeKey }),
			},
		);
	}

	private async request<T>(path: string, init?: RequestInit): Promise<T> {
		const response = await fetch(`${this.baseUrl}${path}`, {
			...init,
			headers: {
				Accept: "application/json",
				"Content-Type": "application/json",
				...(init?.headers ?? {}),
			},
		});

		if (!response.ok) {
			throw new Error(`Shadow identity request failed (${response.status})`);
		}

		return response.json() as Promise<T>;
	}
}
