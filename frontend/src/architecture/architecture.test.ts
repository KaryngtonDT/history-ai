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
	}, 60_000);

	it("keeps fetch() centralized in HttpClient and HttpChatRepository", () => {
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

	it("prevents feature modules from importing Timeline transport directly", () => {
		const timelineViolations = violations.filter(
			(violation) => violation.rule === "feature-timeline-transport",
		);

		expect(timelineViolations).toEqual([]);
	});

	it("keeps InteractiveTimeline props-only without service imports", () => {
		const interactiveTimelineViolations = violations.filter(
			(violation) => violation.rule === "interactive-timeline-props-only",
		);

		expect(interactiveTimelineViolations).toEqual([]);
	});

	it("prevents feature modules from importing Map transport directly", () => {
		const mapViolations = violations.filter(
			(violation) => violation.rule === "feature-map-transport",
		);

		expect(mapViolations).toEqual([]);
	});

	it("keeps InteractiveMap props-only without service imports", () => {
		const interactiveMapViolations = violations.filter(
			(violation) => violation.rule === "interactive-map-props-only",
		);

		expect(interactiveMapViolations).toEqual([]);
	});

	it("prevents feature modules from importing Relation transport directly", () => {
		const relationViolations = violations.filter(
			(violation) => violation.rule === "feature-relation-transport",
		);

		expect(relationViolations).toEqual([]);
	});

	it("prevents feature modules from importing Graph transport directly", () => {
		const graphViolations = violations.filter(
			(violation) => violation.rule === "feature-graph-transport",
		);

		expect(graphViolations).toEqual([]);
	});

	it("prevents feature modules from importing Recommendation transport directly", () => {
		const recommendationViolations = violations.filter(
			(violation) => violation.rule === "feature-recommendation-transport",
		);

		expect(recommendationViolations).toEqual([]);
	});

	it("prevents feature modules from importing Semantic transport directly", () => {
		const semanticViolations = violations.filter(
			(violation) => violation.rule === "feature-semantic-transport",
		);

		expect(semanticViolations).toEqual([]);
	});

	it("prevents feature modules from importing Chat transport directly", () => {
		const chatViolations = violations.filter(
			(violation) => violation.rule === "feature-chat-transport",
		);

		expect(chatViolations).toEqual([]);
	});

	it("keeps InteractiveGraph props-only without service imports", () => {
		const interactiveGraphViolations = violations.filter(
			(violation) => violation.rule === "interactive-graph-props-only",
		);

		expect(interactiveGraphViolations).toEqual([]);
	});
});
