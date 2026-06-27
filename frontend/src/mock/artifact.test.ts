import { describe, expect, it } from "vitest";
import {
	artifactMocksByContentId,
	MOCK_SUMMARY,
	MOCK_TIMELINE,
	MOCK_TRANSCRIPT,
} from "./artifact";
import { generateSummaryFromTranscript } from "./summaryGenerator";

describe("artifact mocks", () => {
	it("includes transcript artifact for mock content", () => {
		const transcript = artifactMocksByContentId["1"].find(
			(artifact) => artifact.type === "transcript",
		);

		expect(transcript?.content).toBe(MOCK_TRANSCRIPT);
	});

	it("derives summary from transcript content", () => {
		const summary = artifactMocksByContentId["1"].find(
			(artifact) => artifact.type === "summary",
		);

		expect(summary?.content).toBe(
			generateSummaryFromTranscript(MOCK_TRANSCRIPT),
		);
		expect(summary?.content).toBe(MOCK_SUMMARY);
	});

	it("does not use placeholder summary text", () => {
		const summary = artifactMocksByContentId["1"].find(
			(artifact) => artifact.type === "summary",
		);

		expect(summary?.content).not.toContain("simulated summary");
		expect(summary?.content).not.toContain("processing worker");
	});

	it("includes timeline artifact for mock content-4", () => {
		const timeline = artifactMocksByContentId["content-4"].find(
			(artifact) => artifact.type === "timeline",
		);

		expect(timeline?.content).toBe(MOCK_TIMELINE);
	});
});
