import type { ContentApiDto, CreateContentApiDto } from "../api/ContentApiDto";
import type {
	Content,
	ContentDisplayStatus,
	ContentSourceType,
	CreateContentInput,
} from "../domain/Content";

const SOURCE_TYPE_TO_API: Record<ContentSourceType, string> = {
	pdf: "upload_pdf",
	audio: "upload_audio",
	video: "upload_video",
	youtube: "youtube_url",
};

const SOURCE_TYPE_FROM_API: Record<string, ContentSourceType> = {
	upload_pdf: "pdf",
	upload_audio: "audio",
	upload_video: "video",
	youtube_url: "youtube",
};

function mapStatusFromApi(status: string): ContentDisplayStatus {
	return status === "completed" ? "completed" : "processing";
}

function mapProgressFromApi(status: string): number {
	return status === "completed" ? 100 : 0;
}

export const ContentMapper = {
	fromApi(dto: ContentApiDto): Content {
		return {
			id: dto.id,
			title: dto.title,
			sourceType: ContentMapper.sourceTypeFromApi(dto.sourceType),
			status: mapStatusFromApi(dto.status),
			progress: mapProgressFromApi(dto.status),
		};
	},

	toCreateApiDto(input: CreateContentInput): CreateContentApiDto {
		return {
			title: input.title,
			sourceType: ContentMapper.sourceTypeToApi(input.sourceType),
		};
	},

	sourceTypeToApi(sourceType: ContentSourceType): string {
		return SOURCE_TYPE_TO_API[sourceType];
	},

	sourceTypeFromApi(sourceType: string): ContentSourceType {
		return SOURCE_TYPE_FROM_API[sourceType] ?? "pdf";
	},
};
