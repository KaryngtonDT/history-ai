import {
	SHADOW_VOICE_COLLECTIONS_PATH,
	SHADOW_VOICE_LIBRARY_PATH,
	SHADOW_VOICE_PRESET_PATH,
	SHADOW_VOICE_PREVIEW_PATH,
} from "@/config/api";
import type { HttpClient } from "@/services/http/HttpClient";
import type { ShadowVoiceRepository } from "./ShadowVoiceRepository";
import type {
	ShadowVoiceCollectionsResponse,
	ShadowVoiceLibraryResponse,
	ShadowVoicePresetResponse,
	ShadowVoicePreviewRequest,
	ShadowVoicePreviewResponse,
} from "./types";

export class HttpShadowVoiceRepository implements ShadowVoiceRepository {
	private readonly httpClient: HttpClient;

	constructor(httpClient: HttpClient) {
		this.httpClient = httpClient;
	}

	getLibrary(): Promise<ShadowVoiceLibraryResponse> {
		return this.httpClient.get<ShadowVoiceLibraryResponse>(
			SHADOW_VOICE_LIBRARY_PATH,
		);
	}

	getCollections(): Promise<ShadowVoiceCollectionsResponse> {
		return this.httpClient.get<ShadowVoiceCollectionsResponse>(
			SHADOW_VOICE_COLLECTIONS_PATH,
		);
	}

	preview(
		request: ShadowVoicePreviewRequest,
	): Promise<ShadowVoicePreviewResponse> {
		return this.httpClient.post<ShadowVoicePreviewResponse>(
			SHADOW_VOICE_PREVIEW_PATH,
			request,
		);
	}

	applyPreset(preset: string): Promise<ShadowVoicePresetResponse> {
		return this.httpClient.post<ShadowVoicePresetResponse>(
			SHADOW_VOICE_PRESET_PATH,
			{ preset },
		);
	}
}
