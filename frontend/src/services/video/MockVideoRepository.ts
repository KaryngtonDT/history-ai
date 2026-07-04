import type {
	VideoJobStatus,
	VideoProcessResult,
	VideoUploadOptions,
	VideoUploadResult,
} from "./types";
import type { VideoRepository } from "./VideoRepository";

export class MockVideoRepository implements VideoRepository {
	async uploadVideo(
		_file: File,
		options?: VideoUploadOptions,
	): Promise<VideoUploadResult> {
		const steps = [0, 25, 50, 75, 100];

		for (const progress of steps) {
			options?.onProgress?.(progress);
			if (progress < 100) {
				await new Promise((resolve) => setTimeout(resolve, 40));
			}
		}

		return {
			videoId: "550e8400-e29b-41d4-a716-446655440099",
			status: "queued",
		};
	}

	async getStatus(videoId: string): Promise<VideoJobStatus> {
		return {
			videoId,
			status: "completed",
			originalFilename: "lecture.mp4",
			language: "unknown",
			createdAt: new Date().toISOString(),
		};
	}

	async processVideo(_videoId: string): Promise<VideoProcessResult> {
		return { status: "queued" };
	}
}
