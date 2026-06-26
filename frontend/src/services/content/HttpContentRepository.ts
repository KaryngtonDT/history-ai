import { CONTENTS_PATH } from "@/config/api";
import type { HttpClient } from "@/services/http/HttpClient";
import type {
	ContentApiDto,
	CreateContentApiResponseDto,
} from "./api/ContentApiDto";
import type { ContentRepository } from "./ContentRepository";
import type {
	Content,
	CreateContentInput,
	CreateContentResult,
} from "./domain/Content";
import { ContentMapper } from "./mappers/ContentMapper";

/**
 * HTTP adapter for Symfony Content API.
 * @see GET  /api/contents
 * @see POST /api/contents
 */
export class HttpContentRepository implements ContentRepository {
	private readonly httpClient: HttpClient;

	constructor(httpClient: HttpClient) {
		this.httpClient = httpClient;
	}

	async listContents(): Promise<Content[]> {
		const items = await this.httpClient.get<ContentApiDto[]>(CONTENTS_PATH);
		return items.map(ContentMapper.fromApi);
	}

	async createContent(input: CreateContentInput): Promise<CreateContentResult> {
		const data = await this.httpClient.post<CreateContentApiResponseDto>(
			CONTENTS_PATH,
			ContentMapper.toCreateApiDto(input),
		);
		return { id: data.id };
	}
}
