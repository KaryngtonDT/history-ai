import { ApiError, NetworkError } from "@/shared/errors";

export class HttpClient {
	private readonly baseUrl: string;

	constructor(baseUrl: string) {
		this.baseUrl = baseUrl.replace(/\/$/, "");
	}

	async get<T>(path: string): Promise<T> {
		try {
			const response = await fetch(`${this.baseUrl}${path}`, {
				headers: { Accept: "application/json" },
			});

			if (!response.ok) {
				throw new ApiError(
					`GET ${path} failed (${response.status})`,
					response.status,
				);
			}

			return (await response.json()) as T;
		} catch (error) {
			if (error instanceof ApiError) {
				throw error;
			}

			throw new NetworkError(`GET ${path} failed`, error);
		}
	}

	async post<T>(path: string, body: unknown): Promise<T> {
		return this.request<T>("POST", path, body);
	}

	async put<T>(path: string, body: unknown): Promise<T> {
		return this.request<T>("PUT", path, body);
	}

	private async request<T>(
		method: "POST" | "PUT",
		path: string,
		body: unknown,
	): Promise<T> {
		try {
			const response = await fetch(`${this.baseUrl}${path}`, {
				method,
				headers: {
					Accept: "application/json",
					"Content-Type": "application/json",
				},
				body: JSON.stringify(body),
			});

			if (!response.ok) {
				throw new ApiError(
					`${method} ${path} failed (${response.status})`,
					response.status,
				);
			}

			return (await response.json()) as T;
		} catch (error) {
			if (error instanceof ApiError) {
				throw error;
			}

			throw new NetworkError(`${method} ${path} failed`, error);
		}
	}
}
