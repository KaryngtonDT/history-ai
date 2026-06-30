import type { QualityReport } from "./types";

export interface QualityRepository {
	getPreviewQuality(): Promise<QualityReport>;
	getByVideoId(videoId: string): Promise<QualityReport>;
}
