import { libraryMock } from "@/mock/library";
import type { LibraryRepository } from "./LibraryRepository";
import type { LibraryItem } from "./types";

export class MockLibraryRepository implements LibraryRepository {
	async listItems(): Promise<LibraryItem[]> {
		return libraryMock.items.map((item) => ({ ...item }));
	}
}

export class EmptyMockLibraryRepository implements LibraryRepository {
	async listItems(): Promise<LibraryItem[]> {
		return [];
	}
}
