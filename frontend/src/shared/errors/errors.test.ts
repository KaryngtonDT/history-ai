import { describe, expect, it } from "vitest";
import { ApiError, DomainError, NetworkError, ValidationError } from "./index";

describe("frontend errors", () => {
	it("extends DomainError for typed failures", () => {
		expect(new ValidationError("invalid")).toBeInstanceOf(DomainError);
		expect(new ApiError("bad request", 400)).toBeInstanceOf(DomainError);
		expect(new NetworkError("offline")).toBeInstanceOf(DomainError);
	});

	it("preserves ApiError status", () => {
		const error = new ApiError("GET failed", 503);
		expect(error.status).toBe(503);
		expect(error.name).toBe("ApiError");
	});
});
