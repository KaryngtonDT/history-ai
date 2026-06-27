import { libraryMock } from "@/mock/library";
import type { LibraryRepository } from "./LibraryRepository";
import type { AddLibraryItemInput, LibraryItem } from "./types";

export class MockLibraryRepository implements LibraryRepository {
	async listItems(): Promise<LibraryItem[]> {
		return libraryMock.items.map((item) => ({ ...item }));
	}

	async addItem(input: AddLibraryItemInput): Promise<LibraryItem> {
		const item: LibraryItem = {
			id: `library-item-${libraryMock.items.length + 1}`,
			contentId: input.contentId,
			artifactId: input.artifactId,
			type: input.type,
			title: input.title,
			createdAt: new Date().toISOString(),
		};

		libraryMock.items.unshift(item);

		return { ...item };
	}
}

export class EmptyMockLibraryRepository implements LibraryRepository {
	async listItems(): Promise<LibraryItem[]> {
		return [];
	}

	async addItem(input: AddLibraryItemInput): Promise<LibraryItem> {
		return {
			id: "library-item-1",
			contentId: input.contentId,
			artifactId: input.artifactId,
			type: input.type,
			title: input.title,
			createdAt: new Date().toISOString(),
		};
	}
}
