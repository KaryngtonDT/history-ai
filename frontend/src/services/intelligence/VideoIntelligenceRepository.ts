import type { VideoIntelligence } from "./types";

export interface VideoIntelligenceRepository {
	getPreviewIntelligence(): Promise<VideoIntelligence>;
	getByVideoId(videoId: string): Promise<VideoIntelligence>;
}
