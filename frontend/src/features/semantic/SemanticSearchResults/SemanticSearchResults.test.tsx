import { readFileSync } from "node:fs";
import { join } from "node:path";
import { render, screen } from "@testing-library/react";
import { describe, expect, it } from "vitest";
import type { ArtifactType } from "@/services/artifact/types";
import type { RetrievedChunk } from "@/services/semantic/types";
import { SemanticSearchResults } from "./SemanticSearchResults";

const results: RetrievedChunk[] = [
	{
		artifactId: "550e8400-e29b-41d4-a716-446655440002",
		chunkId: "550e8400-e29b-41d4-a716-446655440010",
		position: 0,
		text: "## Ancient Rome\n753 BC — Foundation of Rome",
		score: 0.91,
	},
	{
		artifactId: "550e8400-e29b-41d4-a716-446655440004",
		chunkId: "550e8400-e29b-41d4-a716-446655440011",
		position: 1,
		text: "## Greek history\nClassical period overview",
		score: 0.62,
	},
];

const artifactTypesById: Record<string, ArtifactType> = {
	"550e8400-e29b-41d4-a716-446655440002": "summary",
	"550e8400-e29b-41d4-a716-446655440004": "timeline",
};

describe("SemanticSearchResults", () => {
	it("renders score badges without percentage formatting", () => {
		render(
			<SemanticSearchResults
				results={results}
				artifactTypesById={artifactTypesById}
			/>,
		);

		expect(screen.getByText("0.91")).toBeInTheDocument();
		expect(screen.getByText("0.62")).toBeInTheDocument();
		expect(screen.queryByText(/%/)).not.toBeInTheDocument();
	});

	it("preserves backend result order", () => {
		render(
			<SemanticSearchResults
				results={results}
				artifactTypesById={artifactTypesById}
			/>,
		);

		const links = screen.getAllByRole("link");
		expect(links[0]).toHaveAccessibleName(/Ancient Rome/);
		expect(links[1]).toHaveAccessibleName(/Greek history/);
	});

	it("renders artifact anchors", () => {
		render(
			<SemanticSearchResults
				results={results}
				artifactTypesById={artifactTypesById}
			/>,
		);

		expect(screen.getByRole("link", { name: /Ancient Rome/ })).toHaveAttribute(
			"href",
			"#artifact-summary",
		);
		expect(screen.getByRole("link", { name: /Greek history/ })).toHaveAttribute(
			"href",
			"#artifact-timeline",
		);
	});

	it("does not use direct fetch or HTTP repository imports", () => {
		const source = readFileSync(
			join(__dirname, "SemanticSearchResults.tsx"),
			"utf8",
		);
		const fetchPattern = ["fetch", "("].join("");

		expect(source).not.toContain(fetchPattern);
		expect(source).not.toContain("HttpSemanticSearchRepository");
	});
});
