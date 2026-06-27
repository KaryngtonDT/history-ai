import { LIBRARY_ITEMS_PATH } from "@/config/api";
import type { HttpClient } from "@/services/http/HttpClient";
import type { LibraryRepository } from "./LibraryRepository";
import type {
	AddLibraryItemApiResponse,
	AddLibraryItemInput,
	LibraryItem,
	LibraryItemApiDto,
} from "./types";
import {
	mapAddLibraryItemResponseFromApi,
	mapLibraryItemFromApi,
} from "./types";

export class HttpLibraryRepository implements LibraryRepository {
	private readonly httpClient: HttpClient;

	constructor(httpClient: HttpClient) {
		this.httpClient = httpClient;
	}

	async listItems(): Promise<LibraryItem[]> {
		const items =
			await this.httpClient.get<LibraryItemApiDto[]>(LIBRARY_ITEMS_PATH);

		return items.map(mapLibraryItemFromApi);
	}

	async addItem(input: AddLibraryItemInput): Promise<LibraryItem> {
		const response = await this.httpClient.post<AddLibraryItemApiResponse>(
			LIBRARY_ITEMS_PATH,
			input,
		);

		return mapAddLibraryItemResponseFromApi(response, input);
	}
}
