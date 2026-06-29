export type TranscriptLanguage = "english" | "french" | "german" | "unknown";

export interface TranscriptSegment {
	index: number;
	startTime: number;
	endTime: number;
	text: string;
}

export interface VideoTranscript {
	videoId: string;
	transcriptId: string;
	language: TranscriptLanguage;
	text: string;
	duration: number;
	segmentCount: number;
	segments: TranscriptSegment[];
}

export interface VideoTranscriptApiDto {
	videoId: string;
	transcriptId: string;
	language: string;
	text: string;
	duration: number;
	segmentCount: number;
	segments: Array<{
		index: number;
		startTime: number;
		endTime: number;
		text: string;
	}>;
}

const TRANSCRIPT_LANGUAGES = new Set<TranscriptLanguage>([
	"english",
	"french",
	"german",
	"unknown",
]);

export function mapVideoTranscriptFromApi(
	dto: VideoTranscriptApiDto,
): VideoTranscript {
	const language = TRANSCRIPT_LANGUAGES.has(dto.language as TranscriptLanguage)
		? (dto.language as TranscriptLanguage)
		: "unknown";

	return {
		videoId: dto.videoId,
		transcriptId: dto.transcriptId,
		language,
		text: dto.text,
		duration: dto.duration,
		segmentCount: dto.segmentCount,
		segments: dto.segments.map((segment) => ({
			index: segment.index,
			startTime: segment.startTime,
			endTime: segment.endTime,
			text: segment.text,
		})),
	};
}

export function formatTranscriptTimestamp(seconds: number): string {
	const totalSeconds = Math.max(0, Math.floor(seconds));
	const minutes = Math.floor(totalSeconds / 60);
	const remainingSeconds = totalSeconds % 60;

	return `${String(minutes).padStart(2, "0")}:${String(remainingSeconds).padStart(2, "0")}`;
}
