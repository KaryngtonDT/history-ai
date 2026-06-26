import { render, screen, waitFor } from "@testing-library/react";
import { MemoryRouter } from "react-router";
import { afterEach, describe, expect, it, vi } from "vitest";
import { LibraryPage } from "@/pages/Library/LibraryPage";
import { contentService } from "@/services/content/ContentService";

describe("LibraryPage — S1-SLICE-04 mock data", () => {
	afterEach(() => {
		vi.restoreAllMocks();
	});

	it("renders library contents from the service layer", async () => {
		render(
			<MemoryRouter>
				<LibraryPage />
			</MemoryRouter>,
		);

		expect(
			screen.getByRole("heading", { name: "Library" }),
		).toBeInTheDocument();

		await waitFor(() => {
			expect(screen.getByText("The Roman Empire")).toBeInTheDocument();
		});

		expect(screen.getAllByText("PDF")).toHaveLength(2);
		expect(screen.getByText("YouTube")).toBeInTheDocument();
		expect(screen.getByRole("progressbar")).toHaveAttribute(
			"aria-valuenow",
			"62",
		);
	});

	it("shows EmptyState when the library is empty", async () => {
		vi.spyOn(contentService, "listContents").mockResolvedValue([]);

		render(
			<MemoryRouter>
				<LibraryPage />
			</MemoryRouter>,
		);

		await waitFor(() => {
			expect(screen.getByText("No content yet")).toBeInTheDocument();
		});
	});

	it("shows EmptyState when loading fails", async () => {
		vi.spyOn(contentService, "listContents").mockRejectedValue(
			new Error("network"),
		);

		render(
			<MemoryRouter>
				<LibraryPage />
			</MemoryRouter>,
		);

		await waitFor(() => {
			expect(screen.getByText("Unable to load library")).toBeInTheDocument();
		});
	});
});
