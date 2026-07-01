import { ValidationError } from "@/shared/errors";
import type { AudioSourceRepository } from "./AudioSourceRepository";
import { createAudioSourceRepository } from "./AudioSourceRepositoryFactory";
import type {
	AudioSource,
	AudioUploadOptions,
	AudioUploadResult,
} from "./types";
import { validateAudioFile } from "./types";

export class AudioSourceService {
	private readonly repository: AudioSourceRepository;

	constructor(repository: AudioSourceRepository) {
		this.repository = repository;
	}

	listAudioSources(): Promise<AudioSource[]> {
		return this.repository.listAudioSources();
	}

	getAudioSource(audioId: string): Promise<AudioSource | null> {
		return this.repository.getAudioSource(audioId);
	}

	async uploadAudio(
		file: File,
		options?: AudioUploadOptions,
	): Promise<AudioUploadResult> {
		const validation = validateAudioFile(file);

		if (!validation.valid) {
			throw new ValidationError(validation.error ?? "Invalid audio file.");
		}

		return this.repository.uploadAudio(file, options);
	}
}

export const audioSourceService = new AudioSourceService(
	createAudioSourceRepository(),
);
