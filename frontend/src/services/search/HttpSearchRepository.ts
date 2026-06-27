import { SEARCH_LIBRARY_PATH } from "@/config/api";
import type { HttpClient } from "@/services/http/HttpClient";
import type { SearchRepository } from "./SearchRepository";
import type { SearchLibraryItem, SearchLibraryItemApiDto } from "./types";
import { mapSearchLibraryItemFromApi } from "./types";

export class HttpSearchRepository implements SearchRepository {
	private readonly httpClient: HttpClient;

	constructor(httpClient: HttpClient) {
		this.httpClient = httpClient;
	}

	async searchLibrary(query: string): Promise<SearchLibraryItem[]> {
		const path = `${SEARCH_LIBRARY_PATH}?q=${encodeURIComponent(query)}`;
		const items = await this.httpClient.get<SearchLibraryItemApiDto[]>(path);

		return items.map(mapSearchLibraryItemFromApi);
	}
}
