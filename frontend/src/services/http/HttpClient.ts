import { ContentApiError } from "@/services/content/ContentApiError";

export class HttpClient {
	private readonly baseUrl: string;

	constructor(baseUrl: string) {
		this.baseUrl = baseUrl.replace(/\/$/, "");
	}

	async get<T>(path: string): Promise<T> {
		const response = await fetch(`${this.baseUrl}${path}`, {
			headers: { Accept: "application/json" },
		});

		if (!response.ok) {
			throw new ContentApiError(
				`GET ${path} failed (${response.status})`,
				response.status,
			);
		}

		return (await response.json()) as T;
	}

	async post<T>(path: string, body: unknown): Promise<T> {
		const response = await fetch(`${this.baseUrl}${path}`, {
			method: "POST",
			headers: {
				Accept: "application/json",
				"Content-Type": "application/json",
			},
			body: JSON.stringify(body),
		});

		if (!response.ok) {
			throw new ContentApiError(
				`POST ${path} failed (${response.status})`,
				response.status,
			);
		}

		return (await response.json()) as T;
	}
}
