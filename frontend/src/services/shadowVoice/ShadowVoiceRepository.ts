import type {
	ShadowVoiceCollectionsResponse,
	ShadowVoiceLibraryResponse,
	ShadowVoicePresetResponse,
	ShadowVoicePreviewRequest,
	ShadowVoicePreviewResponse,
} from "./types";

export interface ShadowVoiceRepository {
	getLibrary(): Promise<ShadowVoiceLibraryResponse>;
	getCollections(): Promise<ShadowVoiceCollectionsResponse>;
	preview(request: ShadowVoicePreviewRequest): Promise<ShadowVoicePreviewResponse>;
	applyPreset(preset: string): Promise<ShadowVoicePresetResponse>;
}
