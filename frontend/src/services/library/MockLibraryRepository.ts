import { libraryMock } from "@/mock/library";
import type { LibraryRepository } from "./LibraryRepository";
import type { LibraryData } from "./types";

export class MockLibraryRepository implements LibraryRepository {
	getLibrary(): LibraryData {
		return {
			contents: libraryMock.contents,
		};
	}
}
