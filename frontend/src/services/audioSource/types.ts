export type AudioSourceStatus =
	| "uploaded"
	| "queued"
	| "processing"
	| "completed"
	| "failed";

export interface AudioSource {
	id: string;
	title: string;
	originalFilename: string;
	status: AudioSourceStatus;
	type: "audio";
	createdAt: string;
}

export interface AudioUploadResult {
	audioId: string;
	status: AudioSourceStatus;
}

export interface AudioUploadOptions {
	processingMode?: "manual" | "automatic";
	strategy?: string;
	onProgress?: (progress: number) => void;
}

export interface AudioSourceApiDto {
	audioId: string;
	title: string;
	originalFilename: string;
	status: AudioSourceStatus;
	type: string;
	createdAt: string;
}

export interface AudioUploadApiDto {
	audioId: string;
	status: AudioSourceStatus;
}

const AUDIO_EXTENSIONS = [".mp3", ".wav", ".flac", ".m4a", ".ogg"];

export function validateAudioFile(file: File): {
	valid: boolean;
	error?: string;
} {
	const lower = file.name.toLowerCase();
	const valid = AUDIO_EXTENSIONS.some((ext) => lower.endsWith(ext));

	if (!valid) {
		return {
			valid: false,
			error: "Only MP3, WAV, FLAC, M4A, and OGG files are supported.",
		};
	}

	return { valid: true };
}

export function mapAudioSourceFromApi(dto: AudioSourceApiDto): AudioSource {
	return {
		id: dto.audioId,
		title: dto.title,
		originalFilename: dto.originalFilename,
		status: dto.status,
		type: "audio",
		createdAt: dto.createdAt,
	};
}

export function mapAudioUploadFromApi(
	dto: AudioUploadApiDto,
): AudioUploadResult {
	return {
		audioId: dto.audioId,
		status: dto.status,
	};
}
