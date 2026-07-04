import { ValidationError } from "@/shared/errors";
import type {
	VideoJobStatus,
	VideoProcessResult,
	VideoUploadOptions,
	VideoUploadResult,
	VideoValidationResult,
} from "./types";
import type { VideoRepository } from "./VideoRepository";
import { createVideoRepository } from "./VideoRepositoryFactory";

const SUPPORTED_EXTENSIONS = [".mp4", ".mov", ".mkv"] as const;
const SUPPORTED_MIME_TYPES = new Set([
	"video/mp4",
	"video/quicktime",
	"video/x-matroska",
]);
const INVALID_FILE_MESSAGE =
	"Only MP4, MOV, and MKV video files are supported.";

export class VideoService {
	private readonly repository: VideoRepository;

	constructor(repository: VideoRepository) {
		this.repository = repository;
	}

	validateVideo(file: File): VideoValidationResult {
		const lowerName = file.name.toLowerCase();
		const hasSupportedExtension = SUPPORTED_EXTENSIONS.some((extension) =>
			lowerName.endsWith(extension),
		);
		const hasSupportedMime = SUPPORTED_MIME_TYPES.has(file.type);

		if (!hasSupportedExtension && !hasSupportedMime) {
			return { valid: false, error: INVALID_FILE_MESSAGE };
		}

		return { valid: true };
	}

	async uploadVideo(
		file: File,
		options: VideoUploadOptions,
	): Promise<VideoUploadResult> {
		const validation = this.validateVideo(file);

		if (!validation.valid) {
			throw new ValidationError(validation.error);
		}

		return this.repository.uploadVideo(file, options);
	}

	async getStatus(videoId: string): Promise<VideoJobStatus> {
		return this.repository.getStatus(videoId);
	}

	async processVideo(videoId: string): Promise<VideoProcessResult> {
		return this.repository.processVideo(videoId);
	}
}

export const videoService = new VideoService(createVideoRepository());
