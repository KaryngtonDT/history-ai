import { ValidationError } from "@/shared/errors";
import type {
	YouTubeImport,
	YouTubeImportOptions,
	YouTubeImportResult,
	YouTubeMetadata,
} from "./types";
import { validateYouTubeUrl } from "./types";
import type { YouTubeSourceRepository } from "./YouTubeSourceRepository";
import { createYouTubeSourceRepository } from "./YouTubeSourceRepositoryFactory";

export class YouTubeSourceService {
	private readonly repository: YouTubeSourceRepository;

	constructor(repository: YouTubeSourceRepository) {
		this.repository = repository;
	}

	previewYouTube(url: string): Promise<YouTubeMetadata> {
		const validation = validateYouTubeUrl(url);

		if (!validation.valid) {
			throw new ValidationError(validation.error ?? "Invalid YouTube URL.");
		}

		return this.repository.previewYouTube(url.trim());
	}

	importYouTube(
		url: string,
		options?: YouTubeImportOptions,
	): Promise<YouTubeImportResult> {
		const validation = validateYouTubeUrl(url);

		if (!validation.valid) {
			throw new ValidationError(validation.error ?? "Invalid YouTube URL.");
		}

		return this.repository.importYouTube(url.trim(), options);
	}

	listYouTubeImports(): Promise<YouTubeImport[]> {
		return this.repository.listYouTubeImports();
	}
}

export const youtubeSourceService = new YouTubeSourceService(
	createYouTubeSourceRepository(),
);
