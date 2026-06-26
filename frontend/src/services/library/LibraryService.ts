import type { LibraryRepository } from "./LibraryRepository";
import { MockLibraryRepository } from "./MockLibraryRepository";
import type { LibraryData } from "./types";

export class LibraryService {
	private readonly repository: LibraryRepository;

	constructor(repository: LibraryRepository) {
		this.repository = repository;
	}

	getLibrary(): LibraryData {
		return this.repository.getLibrary();
	}
}

export const libraryService = new LibraryService(new MockLibraryRepository());
