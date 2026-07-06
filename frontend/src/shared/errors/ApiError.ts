import { DomainError } from "./DomainError";

export class ApiError extends DomainError {
	readonly status: number;
	readonly body?: unknown;

	constructor(message: string, status: number, body?: unknown) {
		super(message);
		this.status = status;
		this.body = body;
	}
}
