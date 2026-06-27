import { COLLECTIONS_PATH, collectionItemsPath } from "@/config/api";
import type { HttpClient } from "@/services/http/HttpClient";
import type { CollectionRepository } from "./CollectionRepository";
import type {
	Collection,
	CollectionApiDto,
	CollectionItemAssignment,
	CollectionItemAssignmentApiDto,
	CreateCollectionInput,
} from "./types";
import {
	mapCollectionFromApi,
	mapCollectionItemAssignmentFromApi,
} from "./types";

export class HttpCollectionRepository implements CollectionRepository {
	private readonly httpClient: HttpClient;

	constructor(httpClient: HttpClient) {
		this.httpClient = httpClient;
	}

	async listCollections(): Promise<Collection[]> {
		const collections =
			await this.httpClient.get<CollectionApiDto[]>(COLLECTIONS_PATH);

		return collections.map(mapCollectionFromApi);
	}

	async createCollection(input: CreateCollectionInput): Promise<Collection> {
		const response = await this.httpClient.post<CollectionApiDto>(
			COLLECTIONS_PATH,
			input,
		);

		return mapCollectionFromApi(response);
	}

	async assignLibraryItem(
		collectionId: string,
		libraryItemId: string,
	): Promise<CollectionItemAssignment> {
		const response = await this.httpClient.post<CollectionItemAssignmentApiDto>(
			collectionItemsPath(collectionId),
			{ libraryItemId },
		);

		return mapCollectionItemAssignmentFromApi(response);
	}
}
