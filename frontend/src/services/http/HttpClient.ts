import { ApiError, NetworkError } from "@/shared/errors";

export interface PostFormDataOptions {
	onProgress?: (progress: number) => void;
}

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

	postFormData<T>(
		path: string,
		formData: FormData,
		options: PostFormDataOptions = {},
	): Promise<T> {
		const url = `${this.baseUrl}${path}`;

		return new Promise((resolve, reject) => {
			const xhr = new XMLHttpRequest();
			xhr.open("POST", url);
			xhr.setRequestHeader("Accept", "application/json");
			xhr.responseType = "text";

			xhr.upload.addEventListener("progress", (event) => {
				if (!event.lengthComputable || options.onProgress === undefined) {
					return;
				}

				const progress = Math.round((event.loaded / event.total) * 100);
				options.onProgress(progress);
			});

			xhr.addEventListener("load", () => {
				if (xhr.status >= 200 && xhr.status < 300) {
					try {
						resolve(JSON.parse(xhr.responseText) as T);
					} catch (error) {
						reject(new NetworkError(`POST ${path} failed`, error));
					}
					return;
				}

				reject(new ApiError(`POST ${path} failed (${xhr.status})`, xhr.status));
			});

			xhr.addEventListener("error", () => {
				reject(new NetworkError(`POST ${path} failed`));
			});

			xhr.addEventListener("abort", () => {
				reject(new NetworkError(`POST ${path} aborted`));
			});

			try {
				xhr.send(formData);
			} catch (error) {
				reject(new NetworkError(`POST ${path} failed`, error));
			}
		});
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
