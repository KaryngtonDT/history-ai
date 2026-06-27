export type LibraryItemType =
	| "summary"
	| "quiz"
	| "flashcards"
	| "transcript"
	| "timeline"
	| "podcast";

export interface LibraryItem {
	id: string;
	contentId: string;
	artifactId: string;
	type: LibraryItemType;
	title: string;
	createdAt: string;
}

export interface LibraryItemApiDto {
	id: string;
	contentId: string;
	artifactId: string;
	type: string;
	title: string;
	createdAt: string;
}

export interface AddLibraryItemInput {
	contentId: string;
	artifactId: string;
	type: LibraryItemType;
	title: string;
}

export interface AddLibraryItemApiResponse {
	id: string;
	type: string;
	title: string;
	createdAt: string;
}

const LIBRARY_ITEM_TYPES = new Set<LibraryItemType>([
	"summary",
	"quiz",
	"flashcards",
	"transcript",
	"timeline",
	"podcast",
]);

function normalizeLibraryItemType(type: string): LibraryItemType {
	if (LIBRARY_ITEM_TYPES.has(type as LibraryItemType)) {
		return type as LibraryItemType;
	}

	return "summary";
}

export function mapLibraryItemFromApi(dto: LibraryItemApiDto): LibraryItem {
	return {
		id: dto.id,
		contentId: dto.contentId,
		artifactId: dto.artifactId,
		type: normalizeLibraryItemType(dto.type),
		title: dto.title,
		createdAt: dto.createdAt,
	};
}

export function mapAddLibraryItemResponseFromApi(
	dto: AddLibraryItemApiResponse,
	input: AddLibraryItemInput,
): LibraryItem {
	return {
		id: dto.id,
		contentId: input.contentId,
		artifactId: input.artifactId,
		type: normalizeLibraryItemType(dto.type),
		title: dto.title,
		createdAt: dto.createdAt,
	};
}
