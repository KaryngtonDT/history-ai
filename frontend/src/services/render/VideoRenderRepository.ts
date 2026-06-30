import type {
	GenerateVideoRenderRequest,
	VideoRender,
	VideoRenderSummary,
} from "./types";

export interface VideoRenderRepository {
	listRenders(videoId: string): Promise<VideoRenderSummary[]>;
	getRender(videoId: string, language: string): Promise<VideoRender | null>;
	generateRender(
		videoId: string,
		request: GenerateVideoRenderRequest,
	): Promise<void>;
}
