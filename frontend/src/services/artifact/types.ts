export type ArtifactType =
	| "summary"
	| "quiz"
	| "flashcards"
	| "podcast"
	| "timeline"
	| "transcript";

export interface Artifact {
	id: string;
	contentId: string;
	processingJobId: string;
	type: ArtifactType;
	content: string;
	createdAt: string;
}

export interface ArtifactApiDto {
	id: string;
	contentId: string;
	processingJobId: string;
	type: string;
	content: string;
	createdAt: string;
}

const ARTIFACT_TYPES = new Set<ArtifactType>([
	"summary",
	"quiz",
	"flashcards",
	"podcast",
	"timeline",
	"transcript",
]);

function normalizeArtifactType(type: string): ArtifactType {
	if (ARTIFACT_TYPES.has(type as ArtifactType)) {
		return type as ArtifactType;
	}

	return "summary";
}

export function mapArtifactFromApi(dto: ArtifactApiDto): Artifact {
	return {
		id: dto.id,
		contentId: dto.contentId,
		processingJobId: dto.processingJobId,
		type: normalizeArtifactType(dto.type),
		content: dto.content,
		createdAt: dto.createdAt,
	};
}
