import type { TranslationLanguage } from "@/services/translation/types";
import type {
	GenerateVideoRenderRequest,
	VideoRender,
	VideoRenderSummary,
} from "./types";
import type { VideoRenderRepository } from "./VideoRenderRepository";
import { createVideoRenderRepository } from "./VideoRenderRepositoryFactory";

const UUID_PATTERN =
	/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i;

export class VideoRenderService {
	private readonly repository: VideoRenderRepository;

	constructor(repository: VideoRenderRepository) {
		this.repository = repository;
	}

	listRenders(videoId: string): Promise<VideoRenderSummary[]> {
		if (!this.isValidVideoId(videoId)) {
			return Promise.resolve([]);
		}

		return this.repository.listRenders(videoId.trim());
	}

	getRender(
		videoId: string,
		language: TranslationLanguage | string,
	): Promise<VideoRender | null> {
		if (!this.isValidVideoId(videoId)) {
			return Promise.resolve(null);
		}

		return this.repository.getRender(videoId.trim(), language);
	}

	generateRender(
		videoId: string,
		request: GenerateVideoRenderRequest,
	): Promise<void> {
		if (!this.isValidVideoId(videoId)) {
			return Promise.reject(new Error("Invalid video id"));
		}

		if (request.targetLanguages.length === 0) {
			return Promise.reject(new Error("Select at least one target language"));
		}

		return this.repository.generateRender(videoId.trim(), request);
	}

	private isValidVideoId(videoId: string): boolean {
		const normalized = videoId.trim();

		return normalized !== "" && UUID_PATTERN.test(normalized);
	}
}

export const videoRenderService = new VideoRenderService(
	createVideoRenderRepository(),
);
