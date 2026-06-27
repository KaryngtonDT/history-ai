import type { LibraryRepository } from "./LibraryRepository";
import { createLibraryRepository } from "./LibraryRepositoryFactory";
import type { LibraryItem } from "./types";

export class LibraryService {
	private readonly repository: LibraryRepository;

	constructor(repository: LibraryRepository) {
		this.repository = repository;
	}

	listItems(): Promise<LibraryItem[]> {
		return this.repository.listItems();
	}
}

export const libraryService = new LibraryService(createLibraryRepository());
