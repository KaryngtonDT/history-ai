import type { VideoUploadOptions, VideoUploadResult } from "./types";

export interface VideoRepository {
	uploadVideo(
		file: File,
		options?: VideoUploadOptions,
	): Promise<VideoUploadResult>;
}
