import type { LibraryItem } from "./types";

export interface LibraryRepository {
	listItems(): Promise<LibraryItem[]>;
}
