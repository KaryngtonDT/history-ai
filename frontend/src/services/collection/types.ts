export interface Collection {
	id: string;
	name: string;
	description: string;
	createdAt: string;
}

export interface CollectionApiDto {
	id: string;
	name: string;
	description: string;
	createdAt: string;
}

export interface CreateCollectionInput {
	name: string;
	description: string;
}

export interface CollectionItemAssignment {
	id: string;
	collectionId: string;
	libraryItemId: string;
	createdAt: string;
}

export interface CollectionItemAssignmentApiDto {
	id: string;
	collectionId: string;
	libraryItemId: string;
	createdAt: string;
}

export interface AssignLibraryItemApiRequest {
	libraryItemId: string;
}

export function mapCollectionFromApi(dto: CollectionApiDto): Collection {
	return {
		id: dto.id,
		name: dto.name,
		description: dto.description,
		createdAt: dto.createdAt,
	};
}

export function mapCollectionItemAssignmentFromApi(
	dto: CollectionItemAssignmentApiDto,
): CollectionItemAssignment {
	return {
		id: dto.id,
		collectionId: dto.collectionId,
		libraryItemId: dto.libraryItemId,
		createdAt: dto.createdAt,
	};
}
