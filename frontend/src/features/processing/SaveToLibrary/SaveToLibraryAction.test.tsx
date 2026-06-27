import { render, screen, waitFor } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { afterEach, describe, expect, it, vi } from "vitest";
import { SaveToLibraryAction } from "@/features/processing/SaveToLibrary";
import type { Artifact } from "@/services/artifact/types";
import { libraryService } from "@/services/library/LibraryService";

const summaryArtifact: Artifact = {
	id: "artifact-1",
	contentId: "content-1",
	processingJobId: "job-1",
	type: "summary",
	content: "Generated summary text",
	createdAt: "2026-06-26T12:00:00+00:00",
};

describe("SaveToLibraryAction", () => {
	afterEach(() => {
		vi.restoreAllMocks();
	});

	it("triggers save through LibraryService", async () => {
		const user = userEvent.setup();
		const addItem = vi.spyOn(libraryService, "addItem").mockResolvedValue({
			id: "library-item-1",
			contentId: "content-1",
			artifactId: "artifact-1",
			type: "summary",
			title: "Summary",
			createdAt: "2026-06-26T12:00:01+00:00",
		});

		render(
			<SaveToLibraryAction artifact={summaryArtifact} contentId="content-1" />,
		);

		await user.click(screen.getByRole("button", { name: "Save to Library" }));

		await waitFor(() => {
			expect(addItem).toHaveBeenCalledWith({
				contentId: "content-1",
				artifactId: "artifact-1",
				type: "summary",
				title: "Summary",
			});
		});
	});

	it("displays success state after save", async () => {
		const user = userEvent.setup();
		vi.spyOn(libraryService, "addItem").mockResolvedValue({
			id: "library-item-1",
			contentId: "content-1",
			artifactId: "artifact-1",
			type: "summary",
			title: "Summary",
			createdAt: "2026-06-26T12:00:01+00:00",
		});

		render(
			<SaveToLibraryAction artifact={summaryArtifact} contentId="content-1" />,
		);

		await user.click(screen.getByRole("button", { name: "Save to Library" }));

		await waitFor(() => {
			expect(screen.getByText("Saved to Library")).toBeInTheDocument();
		});
	});

	it("displays error state when save fails", async () => {
		const user = userEvent.setup();
		vi.spyOn(libraryService, "addItem").mockRejectedValue(new Error("failed"));

		render(
			<SaveToLibraryAction artifact={summaryArtifact} contentId="content-1" />,
		);

		await user.click(screen.getByRole("button", { name: "Save to Library" }));

		await waitFor(() => {
			expect(
				screen.getByText("Could not save to library."),
			).toBeInTheDocument();
		});
	});
});
