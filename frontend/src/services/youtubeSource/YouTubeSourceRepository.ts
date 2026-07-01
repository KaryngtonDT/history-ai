import type {
	YouTubeImport,
	YouTubeImportOptions,
	YouTubeImportResult,
	YouTubeMetadata,
} from "./types";

export interface YouTubeSourceRepository {
	previewYouTube(url: string): Promise<YouTubeMetadata>;
	importYouTube(
		url: string,
		options?: YouTubeImportOptions,
	): Promise<YouTubeImportResult>;
	listYouTubeImports(): Promise<YouTubeImport[]>;
	getYouTubeImport(youtubeId: string): Promise<YouTubeImport | null>;
}
