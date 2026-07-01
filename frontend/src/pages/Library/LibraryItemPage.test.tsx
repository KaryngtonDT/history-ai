import { screen, waitFor } from "@testing-library/react";
import { MemoryRouter, Route, Routes } from "react-router";
import { afterEach, describe, expect, it, vi } from "vitest";
import { MOCK_SUMMARY, MOCK_TIMELINE } from "@/mock/artifact";
import { LibraryItemPage } from "@/pages/Library/LibraryItemPage";
import { artifactService } from "@/services/artifact/ArtifactService";
import type { Artifact } from "@/services/artifact/types";
import { libraryService } from "@/services/library/LibraryService";
import type { LibraryItem } from "@/services/library/types";
import { renderWithProviders as render } from "@/test/render";

const summaryLibraryItem: LibraryItem = {
	id: "library-item-1",
	contentId: "content-1",
	artifactId: "artifact-1",
	type: "summary",
	title: "The Roman Empire",
	createdAt: "2026-06-26T12:00:00+00:00",
};

const summaryArtifact: Artifact = {
	id: "artifact-1",
	contentId: "content-1",
	processingJobId: "job-1",
	type: "summary",
	content: MOCK_SUMMARY,
	createdAt: "2026-06-26T12:00:01+00:00",
};

function renderLibraryItemPage(libraryItemId: string) {
	return render(
		<MemoryRouter initialEntries={[`/library/${libraryItemId}`]}>
			<Routes>
				<Route path="/library/:libraryItemId" element={<LibraryItemPage />} />
			</Routes>
		</MemoryRouter>,
	);
}

describe("LibraryItemPage — S9-SLICE-08", () => {
	afterEach(() => {
		vi.restoreAllMocks();
	});

	it("renders the selected artifact for a library item", async () => {
		vi.spyOn(libraryService, "listItems").mockResolvedValue([
			summaryLibraryItem,
		]);
		vi.spyOn(artifactService, "listByContentId").mockResolvedValue([
			summaryArtifact,
		]);
		const addItemSpy = vi.spyOn(libraryService, "addItem");

		renderLibraryItemPage("library-item-1");

		await waitFor(() => {
			expect(screen.getByText(MOCK_SUMMARY)).toBeInTheDocument();
		});

		expect(
			screen.getByRole("heading", { name: "The Roman Empire" }),
		).toBeInTheDocument();
		expect(screen.getByText("Summary")).toBeInTheDocument();
		expect(
			screen.queryByRole("button", { name: "Save to Library" }),
		).toBeNull();
		expect(libraryService.listItems).toHaveBeenCalledTimes(1);
		expect(artifactService.listByContentId).toHaveBeenCalledWith("content-1");
		expect(addItemSpy).not.toHaveBeenCalled();
	});

	it("shows not found when the library item does not exist", async () => {
		vi.spyOn(libraryService, "listItems").mockResolvedValue([]);
		const listByContentIdSpy = vi.spyOn(artifactService, "listByContentId");
		const addItemSpy = vi.spyOn(libraryService, "addItem");

		renderLibraryItemPage("missing-item");

		await waitFor(() => {
			expect(screen.getByText("Library item not found")).toBeInTheDocument();
		});

		expect(listByContentIdSpy).not.toHaveBeenCalled();
		expect(addItemSpy).not.toHaveBeenCalled();
	});

	it("shows not found when the linked artifact does not exist", async () => {
		vi.spyOn(libraryService, "listItems").mockResolvedValue([
			summaryLibraryItem,
		]);
		vi.spyOn(artifactService, "listByContentId").mockResolvedValue([]);
		const addItemSpy = vi.spyOn(libraryService, "addItem");

		renderLibraryItemPage("library-item-1");

		await waitFor(() => {
			expect(screen.getByText("Artifact not found")).toBeInTheDocument();
		});

		expect(artifactService.listByContentId).toHaveBeenCalledWith("content-1");
		expect(addItemSpy).not.toHaveBeenCalled();
	});

	it("shows error state when loading fails", async () => {
		vi.spyOn(libraryService, "listItems").mockRejectedValue(
			new Error("network"),
		);
		const listByContentIdSpy = vi.spyOn(artifactService, "listByContentId");
		const addItemSpy = vi.spyOn(libraryService, "addItem");

		renderLibraryItemPage("library-item-1");

		await waitFor(() => {
			expect(
				screen.getByText("Unable to load library item"),
			).toBeInTheDocument();
		});

		expect(listByContentIdSpy).not.toHaveBeenCalled();
		expect(addItemSpy).not.toHaveBeenCalled();
	});
});

describe("LibraryItemPage — S13-SLICE-05", () => {
	afterEach(() => {
		vi.restoreAllMocks();
	});

	it("renders timeline artifact via ArtifactRendererRegistry in read-only mode", async () => {
		const timelineLibraryItem: LibraryItem = {
			id: "library-item-4",
			contentId: "content-4",
			artifactId: "artifact-4",
			type: "timeline",
			title: "Ancient Rome Events",
			createdAt: "2026-06-26T09:00:00+00:00",
		};
		const timelineArtifact: Artifact = {
			id: "artifact-4",
			contentId: "content-4",
			processingJobId: "job-4",
			type: "timeline",
			content: MOCK_TIMELINE,
			createdAt: "2026-06-26T12:00:04+00:00",
		};

		vi.spyOn(libraryService, "listItems").mockResolvedValue([
			timelineLibraryItem,
		]);
		vi.spyOn(artifactService, "listByContentId").mockResolvedValue([
			timelineArtifact,
		]);
		const addItemSpy = vi.spyOn(libraryService, "addItem");

		renderLibraryItemPage("library-item-4");

		await waitFor(() => {
			expect(
				screen.getByText("753 BC — Foundation of Rome"),
			).toBeInTheDocument();
		});

		expect(
			screen.getByRole("heading", { name: "Ancient Rome Events" }),
		).toBeInTheDocument();
		expect(screen.getByText("Timeline")).toBeInTheDocument();
		expect(
			screen.getByRole("heading", { name: "Ancient Rome" }),
		).toBeInTheDocument();
		expect(
			screen.queryByRole("button", { name: "Save to Library" }),
		).toBeNull();
		expect(libraryService.listItems).toHaveBeenCalledTimes(1);
		expect(artifactService.listByContentId).toHaveBeenCalledWith("content-4");
		expect(addItemSpy).not.toHaveBeenCalled();
	});
});
