import type { ShadowVoiceRepository } from "./ShadowVoiceRepository";
import type {
	ShadowVoiceCollectionsResponse,
	ShadowVoiceLibraryResponse,
	ShadowVoicePresetResponse,
	ShadowVoicePreviewRequest,
	ShadowVoicePreviewResponse,
} from "./types";

const API_BASE =
	import.meta.env.VITE_API_BASE_URL?.replace(/\/$/, "") ?? "http://localhost:8000";

export class HttpShadowVoiceRepository implements ShadowVoiceRepository {
	private readonly baseUrl: string;

	constructor(baseUrl: string = API_BASE) {
		this.baseUrl = baseUrl;
	}

	getLibrary(): Promise<ShadowVoiceLibraryResponse> {
		return this.request<ShadowVoiceLibraryResponse>("/api/shadow/voice/library");
	}

	getCollections(): Promise<ShadowVoiceCollectionsResponse> {
		return this.request<ShadowVoiceCollectionsResponse>(
			"/api/shadow/voice/collections",
		);
	}

	preview(
		request: ShadowVoicePreviewRequest,
	): Promise<ShadowVoicePreviewResponse> {
		return this.request<ShadowVoicePreviewResponse>(
			"/api/shadow/voice/preview",
			{
				method: "POST",
				body: JSON.stringify(request),
			},
		);
	}

	applyPreset(preset: string): Promise<ShadowVoicePresetResponse> {
		return this.request<ShadowVoicePresetResponse>("/api/shadow/voice/preset", {
			method: "POST",
			body: JSON.stringify({ preset }),
		});
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
			throw new Error(`Shadow voice request failed (${response.status})`);
		}

		return response.json() as Promise<T>;
	}
}
