import { DomainError } from "./DomainError";

export class NetworkError extends DomainError {
	readonly cause: unknown;

	constructor(message: string, cause?: unknown) {
		super(message);
		this.cause = cause;
	}
}
