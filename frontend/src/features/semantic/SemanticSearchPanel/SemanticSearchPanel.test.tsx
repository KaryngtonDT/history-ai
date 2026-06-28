import { readFileSync } from "node:fs";
import { join } from "node:path";
import { render, screen, waitFor } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { beforeEach, describe, expect, it, vi } from "vitest";
import type { Artifact } from "@/services/artifact/types";
import { SemanticSearchPanel } from "./SemanticSearchPanel";

const { mockSearchSemanticChunks } = vi.hoisted(() => ({
	mockSearchSemanticChunks: vi.fn(),
}));

vi.mock("@/services/semantic/SemanticSearchService", () => ({
	semanticSearchService: {
		searchSemanticChunks: mockSearchSemanticChunks,
	},
}));

const contentId = "550e8400-e29b-41d4-a716-446655440000";
const artifacts: Artifact[] = [
	{
		id: "550e8400-e29b-41d4-a716-446655440002",
		contentId,
		processingJobId: "job-1",
		type: "summary",
		content: "## Ancient Rome\n753 BC — Foundation of Rome",
		createdAt: "2026-06-26T12:00:00+00:00",
	},
];

describe("SemanticSearchPanel", () => {
	beforeEach(() => {
		mockSearchSemanticChunks.mockReset();
	});

	it("calls SemanticSearchService when Search is clicked", async () => {
		const user = userEvent.setup();
		mockSearchSemanticChunks.mockResolvedValue([
			{
				artifactId: "550e8400-e29b-41d4-a716-446655440002",
				chunkId: "550e8400-e29b-41d4-a716-446655440010",
				position: 0,
				text: "## Ancient Rome\n753 BC — Foundation of Rome",
				score: 0.91,
			},
		]);

		render(<SemanticSearchPanel contentId={contentId} artifacts={artifacts} />);

		await user.type(
			screen.getByRole("searchbox", { name: "Search query" }),
			"rome",
		);
		await user.click(screen.getByRole("button", { name: "Search" }));

		await waitFor(() => {
			expect(mockSearchSemanticChunks).toHaveBeenCalledWith(contentId, "rome");
		});
		expect(await screen.findByText("0.91")).toBeInTheDocument();
	});

	it("triggers search on Enter key", async () => {
		const user = userEvent.setup();
		mockSearchSemanticChunks.mockResolvedValue([]);

		render(<SemanticSearchPanel contentId={contentId} artifacts={artifacts} />);

		const input = screen.getByRole("searchbox", { name: "Search query" });
		await user.type(input, "rome{enter}");

		await waitFor(() => {
			expect(mockSearchSemanticChunks).toHaveBeenCalledWith(contentId, "rome");
		});
	});

	it("disables search for queries shorter than two characters", async () => {
		const user = userEvent.setup();

		render(<SemanticSearchPanel contentId={contentId} artifacts={artifacts} />);

		await user.type(
			screen.getByRole("searchbox", { name: "Search query" }),
			"r",
		);

		expect(screen.getByRole("button", { name: "Search" })).toBeDisabled();
		expect(mockSearchSemanticChunks).not.toHaveBeenCalled();
	});

	it("shows loading state while searching", async () => {
		const user = userEvent.setup();
		mockSearchSemanticChunks.mockReturnValue(new Promise(() => {}));

		render(<SemanticSearchPanel contentId={contentId} artifacts={artifacts} />);

		await user.type(
			screen.getByRole("searchbox", { name: "Search query" }),
			"rome",
		);
		await user.click(screen.getByRole("button", { name: "Search" }));

		expect(
			screen.getByRole("status", { name: "Searching semantic chunks" }),
		).toBeInTheDocument();
	});

	it("shows empty state when no results are returned", async () => {
		const user = userEvent.setup();
		mockSearchSemanticChunks.mockResolvedValue([]);

		render(<SemanticSearchPanel contentId={contentId} artifacts={artifacts} />);

		await user.type(
			screen.getByRole("searchbox", { name: "Search query" }),
			"rome",
		);
		await user.click(screen.getByRole("button", { name: "Search" }));

		expect(await screen.findByText("No matches found")).toBeInTheDocument();
	});

	it("shows error state when SemanticSearchService fails", async () => {
		const user = userEvent.setup();
		mockSearchSemanticChunks.mockRejectedValue(new Error("Network error"));

		render(<SemanticSearchPanel contentId={contentId} artifacts={artifacts} />);

		await user.type(
			screen.getByRole("searchbox", { name: "Search query" }),
			"rome",
		);
		await user.click(screen.getByRole("button", { name: "Search" }));

		expect(await screen.findByText("Unable to search")).toBeInTheDocument();
	});

	it("does not use direct fetch or HTTP repository imports", () => {
		const source = readFileSync(
			join(__dirname, "SemanticSearchPanel.tsx"),
			"utf8",
		);
		const fetchPattern = ["fetch", "("].join("");

		expect(source).not.toContain(fetchPattern);
		expect(source).not.toContain("HttpSemanticSearchRepository");
	});
});
