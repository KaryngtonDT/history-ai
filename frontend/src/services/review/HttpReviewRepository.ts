import { API_BASE_URL, PREFERENCES_PATH, videoReviewsPath } from "@/config/api";
import { HttpClient } from "@/services/http/HttpClient";
import type { ReviewRepository } from "./ReviewRepository";
import type {
	PreferenceProfileApiDto,
	ReviewApiDto,
	SaveReviewInput,
} from "./types";
import { mapPreferenceProfileFromApi, mapReviewFromApi } from "./types";

export class HttpReviewRepository implements ReviewRepository {
	private readonly client: HttpClient;

	constructor(client: HttpClient) {
		this.client = client;
	}

	async getReviews(videoId: string) {
		const response = await this.client.get<ReviewApiDto[]>(
			videoReviewsPath(videoId),
		);

		return response.map(mapReviewFromApi);
	}

	async saveReview(videoId: string, input: SaveReviewInput) {
		const response = await this.client.post<ReviewApiDto>(
			videoReviewsPath(videoId),
			input,
		);

		return mapReviewFromApi(response);
	}

	async getPreferenceProfile() {
		try {
			const response =
				await this.client.get<PreferenceProfileApiDto>(PREFERENCES_PATH);

			return mapPreferenceProfileFromApi(response);
		} catch {
			return null;
		}
	}
}

export function createHttpReviewRepository(): HttpReviewRepository {
	return new HttpReviewRepository(new HttpClient(API_BASE_URL));
}
