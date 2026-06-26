import type { ContentSourceType } from "./types";

const TO_API: Record<ContentSourceType, string> = {
	pdf: "upload_pdf",
	audio: "upload_audio",
	video: "upload_video",
	youtube: "youtube_url",
};

const FROM_API: Record<string, ContentSourceType> = {
	upload_pdf: "pdf",
	upload_audio: "audio",
	upload_video: "video",
	youtube_url: "youtube",
};

export function mapSourceTypeToApi(sourceType: ContentSourceType): string {
	return TO_API[sourceType];
}

export function mapSourceTypeFromApi(sourceType: string): ContentSourceType {
	return FROM_API[sourceType] ?? "pdf";
}
