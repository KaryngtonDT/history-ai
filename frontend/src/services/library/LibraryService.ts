import type { LibraryRepository } from "./LibraryRepository";
import { createLibraryRepository } from "./LibraryRepositoryFactory";
import type { AddLibraryItemInput, LibraryItem } from "./types";

export class LibraryService {
	private readonly repository: LibraryRepository;

	constructor(repository: LibraryRepository) {
		this.repository = repository;
	}

	listItems(): Promise<LibraryItem[]> {
		return this.repository.listItems();
	}

	addItem(input: AddLibraryItemInput): Promise<LibraryItem> {
		return this.repository.addItem(input);
	}
}

export const libraryService = new LibraryService(createLibraryRepository());
