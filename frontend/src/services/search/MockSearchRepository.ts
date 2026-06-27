import { libraryMock } from "@/mock/library";
import type { SearchRepository } from "./SearchRepository";
import type { SearchLibraryItem } from "./types";

export class MockSearchRepository implements SearchRepository {
	async searchLibrary(query: string): Promise<SearchLibraryItem[]> {
		const normalized = query.trim().toLowerCase();

		return libraryMock.items
			.filter((item) => item.title.toLowerCase().includes(normalized))
			.map((item) => ({ ...item }));
	}
}
