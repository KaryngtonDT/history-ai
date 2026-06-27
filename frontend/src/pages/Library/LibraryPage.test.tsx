import { render, screen, waitFor } from "@testing-library/react";
import { MemoryRouter } from "react-router";
import { afterEach, describe, expect, it, vi } from "vitest";
import { LibraryPage } from "@/pages/Library/LibraryPage";
import { libraryService } from "@/services/library/LibraryService";

describe("LibraryPage", () => {
	afterEach(() => {
		vi.restoreAllMocks();
	});

	it("displays library items from the service layer", async () => {
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

		expect(screen.getByText("Ancient Greece Quiz")).toBeInTheDocument();
		expect(screen.getByText("YouTube Lecture Flashcards")).toBeInTheDocument();
		expect(screen.getByText("Summary")).toBeInTheDocument();
		expect(screen.getByText("Quiz")).toBeInTheDocument();
		expect(screen.getByText("Flashcards")).toBeInTheDocument();
	});

	it("shows EmptyState when the library is empty", async () => {
		vi.spyOn(libraryService, "listItems").mockResolvedValue([]);

		render(
			<MemoryRouter>
				<LibraryPage />
			</MemoryRouter>,
		);

		await waitFor(() => {
			expect(screen.getByText("No library items yet")).toBeInTheDocument();
		});
	});

	it("shows EmptyState when loading fails", async () => {
		vi.spyOn(libraryService, "listItems").mockRejectedValue(
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

	it("links library cards to the item details page", async () => {
		render(
			<MemoryRouter>
				<LibraryPage />
			</MemoryRouter>,
		);

		await waitFor(() => {
			expect(screen.getByText("The Roman Empire")).toBeInTheDocument();
		});

		expect(
			screen.getByRole("link", { name: "The Roman Empire Summary" }),
		).toHaveAttribute("href", "/library/library-item-1");
	});
});
