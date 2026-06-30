import type {
	GenerateAudioRequest,
	VideoAudio,
	VideoAudioSummary,
} from "./types";

export interface AudioRepository {
	listAudio(videoId: string): Promise<VideoAudioSummary[]>;
	getAudio(videoId: string, language: string): Promise<VideoAudio | null>;
	generateAudio(videoId: string, request: GenerateAudioRequest): Promise<void>;
}
