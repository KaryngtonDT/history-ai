import { collectionMock } from "@/mock/collection";
import type { CollectionRepository } from "./CollectionRepository";
import type {
	Collection,
	CollectionItemAssignment,
	CreateCollectionInput,
} from "./types";

export class CollectionAssignmentConflictError extends Error {
	constructor(message = "Library item already assigned to collection") {
		super(message);
		this.name = "CollectionAssignmentConflictError";
	}
}

export class MockCollectionRepository implements CollectionRepository {
	async listCollections(): Promise<Collection[]> {
		return collectionMock.collections.map((collection) => ({ ...collection }));
	}

	async createCollection(input: CreateCollectionInput): Promise<Collection> {
		const collection: Collection = {
			id: `collection-${collectionMock.collections.length + 1}`,
			name: input.name,
			description: input.description,
			createdAt: new Date().toISOString(),
		};

		collectionMock.collections.unshift(collection);

		return { ...collection };
	}

	async assignLibraryItem(
		collectionId: string,
		libraryItemId: string,
	): Promise<CollectionItemAssignment> {
		const alreadyAssigned = collectionMock.assignments.some(
			(assignment) =>
				assignment.collectionId === collectionId &&
				assignment.libraryItemId === libraryItemId,
		);

		if (alreadyAssigned) {
			throw new CollectionAssignmentConflictError();
		}

		const assignment: CollectionItemAssignment = {
			id: `collection-item-${collectionMock.assignments.length + 1}`,
			collectionId,
			libraryItemId,
			createdAt: new Date().toISOString(),
		};

		collectionMock.assignments.unshift(assignment);

		return { ...assignment };
	}
}
