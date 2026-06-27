import { readFileSync } from "node:fs";
import path from "node:path";
import { fileURLToPath } from "node:url";
import { render, screen } from "@testing-library/react";
import { describe, expect, it } from "vitest";
import { createTimeline } from "@/domain/timeline";
import { createTimelineEvent } from "@/domain/timeline/TimelineEvent";
import { createTimelineSection } from "@/domain/timeline/TimelineSection";
import { ProcessingTimelineArtifact } from "@/features/processing/ProcessingTimelineArtifact";
import type { Artifact } from "@/services/artifact/types";

const timelineArtifact: Artifact = {
	id: "artifact-timeline-1",
	contentId: "content-1",
	processingJobId: "job-1",
	type: "timeline",
	content: [
		"# Timeline",
		"",
		"## Ancient Rome",
		"",
		"- 753 BC — Foundation of Rome",
		"- Republic established",
		"",
		"## Empire",
		"",
		"- Augustus becomes emperor",
		"- Pax Romana",
	].join("\n"),
	createdAt: "2026-06-26T12:00:04+00:00",
};

describe("ProcessingTimelineArtifact", () => {
	it("displays timeline artifact content with readable markdown structure", () => {
		render(<ProcessingTimelineArtifact artifact={timelineArtifact} />);

		expect(screen.getByText("Timeline")).toBeInTheDocument();
		expect(
			screen.getByRole("heading", { name: "Ancient Rome" }),
		).toBeInTheDocument();
		expect(screen.getByRole("heading", { name: "Empire" })).toBeInTheDocument();
		expect(screen.getByText("753 BC — Foundation of Rome")).toBeInTheDocument();
		expect(screen.getByText("Republic established")).toBeInTheDocument();
		expect(screen.getByText("Augustus becomes emperor")).toBeInTheDocument();
		expect(screen.getByText("Pax Romana")).toBeInTheDocument();
	});

	it("renders structured timeline when provided", () => {
		const structuredTimeline = createTimeline([
			createTimelineSection("Structured Section", [
				createTimelineEvent("Structured event one"),
			]),
		]);

		render(
			<ProcessingTimelineArtifact
				artifact={timelineArtifact}
				structuredTimeline={structuredTimeline}
			/>,
		);

		expect(
			screen.getByRole("heading", { name: "Structured Section" }),
		).toBeInTheDocument();
		expect(screen.getByText("Structured event one")).toBeInTheDocument();
		expect(
			screen.queryByRole("heading", { name: "Ancient Rome" }),
		).not.toBeInTheDocument();
	});

	it("shows loading state when isLoading is true", () => {
		render(
			<ProcessingTimelineArtifact artifact={timelineArtifact} isLoading />,
		);

		expect(
			screen.getByRole("status", { name: "Loading timeline" }),
		).toBeInTheDocument();
	});

	it("shows empty state when no timeline artifact is provided", () => {
		render(<ProcessingTimelineArtifact artifact={null} />);

		expect(screen.getByText("Timeline")).toBeInTheDocument();
		expect(screen.getByText("No timeline yet")).toBeInTheDocument();
	});

	it("does not import ArtifactService or fetch directly", () => {
		const componentPath = path.resolve(
			path.dirname(fileURLToPath(import.meta.url)),
			"ProcessingTimelineArtifact.tsx",
		);
		const source = readFileSync(componentPath, "utf8");

		expect(source).not.toMatch(/ArtifactService/);
		expect(source).not.toMatch(/\bfetch\s*\(/);
		expect(source).not.toMatch(/HttpTimelineRepository/);
	});
});
