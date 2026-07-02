import { createShadowVoiceRepository } from "./ShadowVoiceRepositoryFactory";
import type { ShadowVoiceRepository } from "./ShadowVoiceRepository";
import type {
	ShadowVoiceCollectionsResponse,
	ShadowVoiceLibraryResponse,
	ShadowVoicePresetResponse,
	ShadowVoicePreviewRequest,
	ShadowVoicePreviewResponse,
} from "./types";

export class ShadowVoiceService {
	private readonly repository: ShadowVoiceRepository;

	constructor(repository: ShadowVoiceRepository = createShadowVoiceRepository()) {
		this.repository = repository;
	}

	getLibrary(): Promise<ShadowVoiceLibraryResponse> {
		return this.repository.getLibrary();
	}

	getCollections(): Promise<ShadowVoiceCollectionsResponse> {
		return this.repository.getCollections();
	}

	preview(
		request: ShadowVoicePreviewRequest,
	): Promise<ShadowVoicePreviewResponse> {
		return this.repository.preview(request);
	}

	applyPreset(preset: string): Promise<ShadowVoicePresetResponse> {
		return this.repository.applyPreset(preset);
	}
}

export const shadowVoiceService = new ShadowVoiceService();
