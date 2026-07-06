import type { VideoTranscript } from "./types";

export interface TranscriptLoadResult {
	transcript: VideoTranscript | null;
	unavailableDetail?: unknown;
}

export interface TranscriptRepository {
	getTranscript(videoId: string): Promise<VideoTranscript | null>;
	loadTranscript(videoId: string): Promise<TranscriptLoadResult>;
}
