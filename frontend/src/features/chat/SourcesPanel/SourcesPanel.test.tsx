import { readFileSync } from "node:fs";
import { join } from "node:path";
import { render, screen } from "@testing-library/react";
import { describe, expect, it } from "vitest";
import { SourcesPanel } from "./SourcesPanel";

describe("SourcesPanel", () => {
	it("renders artifact type, score, and anchor", () => {
		render(
			<SourcesPanel
				sources={[
					{
						artifactId: "550e8400-e29b-41d4-a716-446655440002",
						chunkId: "550e8400-e29b-41d4-a716-446655440010",
						text: "## Ancient Rome",
						score: 0.91,
					},
					{
						artifactId: "550e8400-e29b-41d4-a716-446655440003",
						chunkId: "550e8400-e29b-41d4-a716-446655440011",
						text: "## Timeline",
						score: 0.89,
					},
				]}
				artifactTypesById={{
					"550e8400-e29b-41d4-a716-446655440002": "summary",
					"550e8400-e29b-41d4-a716-446655440003": "timeline",
				}}
			/>,
		);

		expect(
			screen.getByRole("link", { name: "Summary (0.91)" }),
		).toHaveAttribute("href", "#artifact-summary");
		expect(
			screen.getByRole("link", { name: "Timeline (0.89)" }),
		).toHaveAttribute("href", "#artifact-timeline");
	});

	it("renders citation numbers next to matching sources", () => {
		render(
			<SourcesPanel
				sources={[
					{
						artifactId: "550e8400-e29b-41d4-a716-446655440002",
						chunkId: "550e8400-e29b-41d4-a716-446655440010",
						text: "## Ancient Rome",
						score: 0.91,
					},
					{
						artifactId: "550e8400-e29b-41d4-a716-446655440003",
						chunkId: "550e8400-e29b-41d4-a716-446655440011",
						text: "## Timeline",
						score: 0.89,
					},
				]}
				citations={[
					{
						number: 1,
						artifactId: "550e8400-e29b-41d4-a716-446655440002",
						chunkId: "550e8400-e29b-41d4-a716-446655440010",
						score: 0.91,
					},
					{
						number: 2,
						artifactId: "550e8400-e29b-41d4-a716-446655440003",
						chunkId: "550e8400-e29b-41d4-a716-446655440011",
						score: 0.89,
					},
				]}
				artifactTypesById={{
					"550e8400-e29b-41d4-a716-446655440002": "summary",
					"550e8400-e29b-41d4-a716-446655440003": "timeline",
				}}
			/>,
		);

		expect(
			screen.getByRole("link", { name: "[1] Summary (0.91)" }),
		).toHaveAttribute("href", "#artifact-summary");
		expect(
			screen.getByRole("link", { name: "[2] Timeline (0.89)" }),
		).toHaveAttribute("href", "#artifact-timeline");
	});

	it("returns null when sources are empty", () => {
		const { container } = render(
			<SourcesPanel sources={[]} artifactTypesById={{}} />,
		);

		expect(container).toBeEmptyDOMElement();
	});

	it("does not import services directly", () => {
		const source = readFileSync(join(__dirname, "SourcesPanel.tsx"), "utf8");

		expect(source).not.toContain("ChatService");
		expect(source).not.toContain("HttpChatRepository");
	});
});
