import type { VideoTranscript } from "./types";

export interface TranscriptRepository {
	getTranscript(videoId: string): Promise<VideoTranscript | null>;
}
