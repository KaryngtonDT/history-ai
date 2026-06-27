import type { SearchLibraryItem } from "./types";

export interface SearchRepository {
	searchLibrary(query: string): Promise<SearchLibraryItem[]>;
}
