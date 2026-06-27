import type { CollectionRepository } from "./CollectionRepository";
import { createCollectionRepository } from "./CollectionRepositoryFactory";
import type {
	Collection,
	CollectionItemAssignment,
	CreateCollectionInput,
} from "./types";

export class CollectionService {
	private readonly repository: CollectionRepository;

	constructor(repository: CollectionRepository) {
		this.repository = repository;
	}

	listCollections(): Promise<Collection[]> {
		return this.repository.listCollections();
	}

	createCollection(input: CreateCollectionInput): Promise<Collection> {
		return this.repository.createCollection(input);
	}

	assignLibraryItem(
		collectionId: string,
		libraryItemId: string,
	): Promise<CollectionItemAssignment> {
		return this.repository.assignLibraryItem(collectionId, libraryItemId);
	}
}

export const collectionService = new CollectionService(
	createCollectionRepository(),
);
