// @vitest-environment node

import { beforeAll, describe, expect, it } from "vitest";
import {
	type ArchitectureViolation,
	collectArchitectureViolations,
} from "./architectureRules";

describe("Frontend architecture rules", () => {
	let violations: ArchitectureViolation[];

	beforeAll(() => {
		violations = collectArchitectureViolations();
	}, 30_000);

	it("keeps fetch() centralized in HttpClient", () => {
		const fetchViolations = violations.filter(
			(violation) => violation.rule === "fetch-centralization",
		);

		expect(fetchViolations).toEqual([]);
	});

	it("prevents feature modules from importing Http repositories", () => {
		const repositoryViolations = violations.filter(
			(violation) => violation.rule === "feature-http-repository",
		);

		expect(repositoryViolations).toEqual([]);
	});

	it("prevents feature modules from importing HttpClient directly", () => {
		const clientViolations = violations.filter(
			(violation) => violation.rule === "feature-http-client",
		);

		expect(clientViolations).toEqual([]);
	});

	it("prevents feature modules from importing Search transport directly", () => {
		const searchViolations = violations.filter(
			(violation) => violation.rule === "feature-search-transport",
		);

		expect(searchViolations).toEqual([]);
	});
});
