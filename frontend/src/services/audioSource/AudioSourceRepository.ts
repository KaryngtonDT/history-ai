import type {
	AudioSource,
	AudioUploadOptions,
	AudioUploadResult,
} from "./types";

export interface AudioSourceRepository {
	listAudioSources(): Promise<AudioSource[]>;
	getAudioSource(audioId: string): Promise<AudioSource | null>;
	uploadAudio(
		file: File,
		options?: AudioUploadOptions,
	): Promise<AudioUploadResult>;
}
