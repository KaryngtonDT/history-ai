import type { LibraryItemType } from "@/services/library/types";

export interface SearchLibraryItem {
	id: string;
	contentId: string;
	artifactId: string;
	type: LibraryItemType;
	title: string;
	createdAt: string;
}

export interface SearchLibraryItemApiDto {
	id: string;
	contentId: string;
	artifactId: string;
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

export function mapSearchLibraryItemFromApi(
	dto: SearchLibraryItemApiDto,
): SearchLibraryItem {
	return {
		id: dto.id,
		contentId: dto.contentId,
		artifactId: dto.artifactId,
		type: normalizeLibraryItemType(dto.type),
		title: dto.title,
		createdAt: dto.createdAt,
	};
}
