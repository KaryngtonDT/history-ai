import type {
	Collection,
	CollectionItemAssignment,
	CreateCollectionInput,
} from "./types";

export interface CollectionRepository {
	listCollections(): Promise<Collection[]>;

	createCollection(input: CreateCollectionInput): Promise<Collection>;

	assignLibraryItem(
		collectionId: string,
		libraryItemId: string,
	): Promise<CollectionItemAssignment>;
}
