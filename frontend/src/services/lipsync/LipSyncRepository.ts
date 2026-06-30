import type {
	GenerateLipSyncRequest,
	VideoLipSync,
	VideoLipSyncSummary,
} from "./types";

export interface LipSyncRepository {
	listLipSyncs(videoId: string): Promise<VideoLipSyncSummary[]>;
	getLipSync(videoId: string, language: string): Promise<VideoLipSync | null>;
	generateLipSync(
		videoId: string,
		request: GenerateLipSyncRequest,
	): Promise<void>;
}
