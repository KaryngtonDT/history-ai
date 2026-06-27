import { render, screen, waitFor } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { MemoryRouter } from "react-router";
import { afterEach, describe, expect, it, vi } from "vitest";
import { LibraryPage } from "@/pages/Library/LibraryPage";
import { collectionService } from "@/services/collection/CollectionService";
import { CollectionAssignmentConflictError } from "@/services/collection/MockCollectionRepository";
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
			screen.getByRole("link", { name: "The Roman Empire" }),
		).toHaveAttribute("href", "/library/library-item-1");
	});

	it("shows Add to Collection action on library item cards", async () => {
		render(
			<MemoryRouter>
				<LibraryPage />
			</MemoryRouter>,
		);

		await waitFor(() => {
			expect(screen.getByText("The Roman Empire")).toBeInTheDocument();
		});

		expect(
			screen.getAllByRole("button", { name: "Add to Collection" }),
		).toHaveLength(3);
	});

	it("opens AssignToCollectionDialog when Add to Collection is clicked", async () => {
		const user = userEvent.setup();
		vi.spyOn(collectionService, "listCollections").mockResolvedValue([
			{
				id: "collection-1",
				name: "Ancient Rome",
				description: "Resources about Roman history",
				createdAt: "2026-06-27T12:00:00+00:00",
			},
		]);

		render(
			<MemoryRouter>
				<LibraryPage />
			</MemoryRouter>,
		);

		await waitFor(() => {
			expect(screen.getByText("The Roman Empire")).toBeInTheDocument();
		});

		await user.click(
			screen.getAllByRole("button", { name: "Add to Collection" })[0],
		);

		expect(
			screen.getByRole("dialog", { name: "Assign to collection" }),
		).toBeInTheDocument();
		expect(
			screen.getByRole("combobox", { name: "Collection" }),
		).toBeInTheDocument();
	});

	it("displays assign success from the library card flow", async () => {
		const user = userEvent.setup();
		vi.spyOn(collectionService, "listCollections").mockResolvedValue([
			{
				id: "collection-1",
				name: "Ancient Rome",
				description: "Resources about Roman history",
				createdAt: "2026-06-27T12:00:00+00:00",
			},
		]);
		vi.spyOn(collectionService, "assignLibraryItem").mockResolvedValue({
			id: "collection-item-2",
			collectionId: "collection-1",
			libraryItemId: "library-item-1",
			createdAt: "2026-06-27T13:00:00+00:00",
		});

		render(
			<MemoryRouter>
				<LibraryPage />
			</MemoryRouter>,
		);

		await waitFor(() => {
			expect(screen.getByText("The Roman Empire")).toBeInTheDocument();
		});

		await user.click(
			screen.getAllByRole("button", { name: "Add to Collection" })[0],
		);
		await user.click(screen.getByRole("button", { name: "Assign" }));

		await waitFor(() => {
			expect(
				screen.getByText("Library item assigned successfully."),
			).toBeInTheDocument();
		});

		expect(collectionService.assignLibraryItem).toHaveBeenCalledWith(
			"collection-1",
			"library-item-1",
		);
	});

	it("displays duplicate assignment from the library card flow", async () => {
		const user = userEvent.setup();
		vi.spyOn(collectionService, "listCollections").mockResolvedValue([
			{
				id: "collection-1",
				name: "Ancient Rome",
				description: "Resources about Roman history",
				createdAt: "2026-06-27T12:00:00+00:00",
			},
		]);
		vi.spyOn(collectionService, "assignLibraryItem").mockRejectedValue(
			new CollectionAssignmentConflictError(),
		);

		render(
			<MemoryRouter>
				<LibraryPage />
			</MemoryRouter>,
		);

		await waitFor(() => {
			expect(screen.getByText("The Roman Empire")).toBeInTheDocument();
		});

		await user.click(
			screen.getAllByRole("button", { name: "Add to Collection" })[0],
		);
		await user.click(screen.getByRole("button", { name: "Assign" }));

		await waitFor(() => {
			expect(
				screen.getByText(
					"This library item is already in the selected collection.",
				),
			).toBeInTheDocument();
		});
	});

	it("does not navigate to details when Add to Collection is clicked", async () => {
		const user = userEvent.setup();
		vi.spyOn(collectionService, "listCollections").mockResolvedValue([
			{
				id: "collection-1",
				name: "Ancient Rome",
				description: "Resources about Roman history",
				createdAt: "2026-06-27T12:00:00+00:00",
			},
		]);

		render(
			<MemoryRouter initialEntries={["/library"]}>
				<LibraryPage />
			</MemoryRouter>,
		);

		await waitFor(() => {
			expect(screen.getByText("The Roman Empire")).toBeInTheDocument();
		});

		await user.click(
			screen.getAllByRole("button", { name: "Add to Collection" })[0],
		);

		expect(
			screen.getByRole("dialog", { name: "Assign to collection" }),
		).toBeInTheDocument();
		expect(
			screen.getByRole("heading", { name: "Library" }),
		).toBeInTheDocument();
	});
});
