import type { AddLibraryItemInput, LibraryItem } from "./types";

export interface LibraryRepository {
	listItems(): Promise<LibraryItem[]>;

	addItem(input: AddLibraryItemInput): Promise<LibraryItem>;
}
