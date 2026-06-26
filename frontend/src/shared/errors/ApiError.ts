import { DomainError } from "./DomainError";

export class ApiError extends DomainError {
	readonly status: number;

	constructor(message: string, status: number) {
		super(message);
		this.status = status;
	}
}
