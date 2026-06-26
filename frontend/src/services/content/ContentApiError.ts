export class ContentApiError extends Error {
	readonly status: number;

	constructor(message: string, status: number) {
		super(message);
		this.name = "ContentApiError";
		this.status = status;
	}
}
