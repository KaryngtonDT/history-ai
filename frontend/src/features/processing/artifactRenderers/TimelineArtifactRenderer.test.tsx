import { readFileSync } from "node:fs";
import path from "node:path";
import { fileURLToPath } from "node:url";
import { render, screen, waitFor } from "@testing-library/react";
import { beforeEach, describe, expect, it, vi } from "vitest";
import type { Artifact } from "@/services/artifact/types";
import { TimelineArtifactRenderer } from "./TimelineArtifactRenderer";

const { mockGetTimeline } = vi.hoisted(() => ({
	mockGetTimeline: vi.fn(),
}));

vi.mock("@/services/timeline/TimelineService", () => ({
	timelineService: {
		getTimeline: mockGetTimeline,
	},
}));

const timelineArtifact: Artifact = {
	id: "550e8400-e29b-41d4-a716-446655440000",
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
	].join("\n"),
	createdAt: "2026-06-26T12:00:04+00:00",
};

const structuredTimeline = {
	sections: [
		{
			title: "Structured Rome",
			events: [{ text: "Structured event" }],
		},
	],
};

describe("TimelineArtifactRenderer", () => {
	beforeEach(() => {
		mockGetTimeline.mockReset();
	});

	it("calls TimelineService with artifact id", async () => {
		mockGetTimeline.mockResolvedValue(structuredTimeline);

		render(
			<TimelineArtifactRenderer
				artifact={timelineArtifact}
				contentId="content-1"
			/>,
		);

		await waitFor(() => {
			expect(mockGetTimeline).toHaveBeenCalledWith(timelineArtifact.id);
		});
	});

	it("displays loading state while structured timeline loads", () => {
		mockGetTimeline.mockReturnValue(new Promise(() => {}));

		render(
			<TimelineArtifactRenderer
				artifact={timelineArtifact}
				contentId="content-1"
			/>,
		);

		expect(
			screen.getByRole("status", { name: "Loading timeline" }),
		).toBeInTheDocument();
	});

	it("displays structured timeline when TimelineService returns data", async () => {
		mockGetTimeline.mockResolvedValue(structuredTimeline);

		render(
			<TimelineArtifactRenderer
				artifact={timelineArtifact}
				contentId="content-1"
			/>,
		);

		expect(
			await screen.findByRole("heading", { name: "Structured Rome" }),
		).toBeInTheDocument();
		expect(screen.getByText("Structured event")).toBeInTheDocument();
	});

	it("falls back to markdown when TimelineService returns null", async () => {
		mockGetTimeline.mockResolvedValue(null);

		render(
			<TimelineArtifactRenderer
				artifact={timelineArtifact}
				contentId="content-1"
			/>,
		);

		expect(
			await screen.findByRole("heading", { name: "Ancient Rome" }),
		).toBeInTheDocument();
		expect(screen.getByText("753 BC — Foundation of Rome")).toBeInTheDocument();
	});

	it("falls back to markdown when TimelineService fails", async () => {
		mockGetTimeline.mockRejectedValue(new Error("Network error"));

		render(
			<TimelineArtifactRenderer
				artifact={timelineArtifact}
				contentId="content-1"
			/>,
		);

		expect(
			await screen.findByRole("heading", { name: "Ancient Rome" }),
		).toBeInTheDocument();
		expect(screen.getByText("753 BC — Foundation of Rome")).toBeInTheDocument();
	});

	it("hides Save to Library in readOnly mode", async () => {
		mockGetTimeline.mockResolvedValue(null);

		render(
			<TimelineArtifactRenderer
				artifact={timelineArtifact}
				contentId="content-1"
				readOnly
			/>,
		);

		await screen.findByRole("heading", { name: "Ancient Rome" });

		expect(
			screen.queryByRole("button", { name: "Save to Library" }),
		).not.toBeInTheDocument();
	});

	it("does not import HttpTimelineRepository or use fetch directly", () => {
		const componentPath = path.resolve(
			path.dirname(fileURLToPath(import.meta.url)),
			"TimelineArtifactRenderer.tsx",
		);
		const source = readFileSync(componentPath, "utf8");

		expect(source).not.toMatch(/HttpTimelineRepository/);
		expect(source).not.toMatch(/\bfetch\s*\(/);
	});
});
