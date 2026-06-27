import type { SearchRepository } from "./SearchRepository";
import { createSearchRepository } from "./SearchRepositoryFactory";
import type { SearchLibraryItem } from "./types";

export class SearchService {
	private readonly repository: SearchRepository;

	constructor(repository: SearchRepository) {
		this.repository = repository;
	}

	searchLibrary(query: string): Promise<SearchLibraryItem[]> {
		const normalized = query.trim();

		if (normalized === "") {
			return Promise.resolve([]);
		}

		return this.repository.searchLibrary(normalized);
	}
}

export const searchService = new SearchService(createSearchRepository());
