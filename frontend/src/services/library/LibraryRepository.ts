import type { LibraryData } from "./types";

export interface LibraryRepository {
	getLibrary(): LibraryData;
}
