import type {
	VideoJobStatus,
	VideoProcessResult,
	VideoUploadOptions,
	VideoUploadResult,
} from "./types";

export interface VideoRepository {
	uploadVideo(
		file: File,
		options?: VideoUploadOptions,
	): Promise<VideoUploadResult>;
	getStatus(videoId: string): Promise<VideoJobStatus>;
	processVideo(videoId: string): Promise<VideoProcessResult>;
}
