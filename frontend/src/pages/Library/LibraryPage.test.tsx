import { screen, waitFor } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { MemoryRouter } from "react-router";
import { afterEach, describe, expect, it, vi } from "vitest";
import { LibraryPage } from "@/pages/Library/LibraryPage";
import { collectionService } from "@/services/collection/CollectionService";
import { CollectionAssignmentConflictError } from "@/services/collection/MockCollectionRepository";
import { libraryService } from "@/services/library/LibraryService";
import { searchService } from "@/services/search/SearchService";
import { renderWithProviders } from "@/test/render";

describe("LibraryPage", () => {
	afterEach(() => {
		vi.restoreAllMocks();
	});

	it("shows the normal library list when the search query is empty", async () => {
		const searchSpy = vi.spyOn(searchService, "searchLibrary");

		renderWithProviders(
			<MemoryRouter>
				<LibraryPage />
			</MemoryRouter>,
		);

		expect(
			screen.getByRole("searchbox", { name: "Search library" }),
		).toHaveValue("");

		await waitFor(() => {
			expect(screen.getByText("The Roman Empire")).toBeInTheDocument();
		});

		expect(screen.getByText("Ancient Greece Quiz")).toBeInTheDocument();
		expect(screen.getByText("YouTube Lecture Flashcards")).toBeInTheDocument();
		expect(screen.getByText("Ancient Rome Events")).toBeInTheDocument();
		expect(searchSpy).not.toHaveBeenCalled();
	});

	it("calls SearchService when typing a search query", async () => {
		const user = userEvent.setup();
		const searchSpy = vi
			.spyOn(searchService, "searchLibrary")
			.mockResolvedValue([
				{
					id: "library-item-1",
					contentId: "content-1",
					artifactId: "artifact-1",
					type: "summary",
					title: "The Roman Empire",
					createdAt: "2026-06-26T12:00:00+00:00",
				},
			]);

		renderWithProviders(
			<MemoryRouter>
				<LibraryPage />
			</MemoryRouter>,
		);

		await waitFor(() => {
			expect(screen.getByText("The Roman Empire")).toBeInTheDocument();
		});

		await user.type(
			screen.getByRole("searchbox", { name: "Search library" }),
			"Roman",
		);

		await waitFor(() => {
			expect(searchSpy).toHaveBeenCalledWith("Roman");
		});
	});

	it("renders search results from SearchService", async () => {
		const user = userEvent.setup();
		vi.spyOn(searchService, "searchLibrary").mockResolvedValue([
			{
				id: "library-item-1",
				contentId: "content-1",
				artifactId: "artifact-1",
				type: "summary",
				title: "The Roman Empire",
				createdAt: "2026-06-26T12:00:00+00:00",
			},
		]);

		renderWithProviders(
			<MemoryRouter>
				<LibraryPage />
			</MemoryRouter>,
		);

		await waitFor(() => {
			expect(screen.getByText("The Roman Empire")).toBeInTheDocument();
		});

		await user.type(
			screen.getByRole("searchbox", { name: "Search library" }),
			"Roman",
		);

		await waitFor(() => {
			expect(screen.getByText("The Roman Empire")).toBeInTheDocument();
		});

		expect(screen.queryByText("Ancient Greece Quiz")).not.toBeInTheDocument();
		expect(
			screen.queryByText("YouTube Lecture Flashcards"),
		).not.toBeInTheDocument();
	});

	it("shows empty state when search returns no results", async () => {
		const user = userEvent.setup();
		vi.spyOn(searchService, "searchLibrary").mockResolvedValue([]);

		renderWithProviders(
			<MemoryRouter>
				<LibraryPage />
			</MemoryRouter>,
		);

		await waitFor(() => {
			expect(screen.getByText("The Roman Empire")).toBeInTheDocument();
		});

		await user.type(
			screen.getByRole("searchbox", { name: "Search library" }),
			"nothing",
		);

		await waitFor(() => {
			expect(screen.getByText("No results found")).toBeInTheDocument();
		});

		expect(
			screen.getByText("Try a different search term."),
		).toBeInTheDocument();
	});

	it("shows error state when search fails", async () => {
		const user = userEvent.setup();
		vi.spyOn(searchService, "searchLibrary").mockRejectedValue(
			new Error("network"),
		);

		renderWithProviders(
			<MemoryRouter>
				<LibraryPage />
			</MemoryRouter>,
		);

		await waitFor(() => {
			expect(screen.getByText("The Roman Empire")).toBeInTheDocument();
		});

		await user.type(
			screen.getByRole("searchbox", { name: "Search library" }),
			"Roman",
		);

		await waitFor(() => {
			expect(
				screen.getAllByText("Unable to search library").length,
			).toBeGreaterThan(0);
		});
	});

	it("links search result cards to the item details page", async () => {
		const user = userEvent.setup();
		vi.spyOn(searchService, "searchLibrary").mockResolvedValue([
			{
				id: "library-item-1",
				contentId: "content-1",
				artifactId: "artifact-1",
				type: "summary",
				title: "The Roman Empire",
				createdAt: "2026-06-26T12:00:00+00:00",
			},
		]);

		renderWithProviders(
			<MemoryRouter>
				<LibraryPage />
			</MemoryRouter>,
		);

		await waitFor(() => {
			expect(screen.getByText("The Roman Empire")).toBeInTheDocument();
		});

		await user.type(
			screen.getByRole("searchbox", { name: "Search library" }),
			"Roman",
		);

		await waitFor(() => {
			expect(
				screen.getByRole("link", { name: "The Roman Empire" }),
			).toHaveAttribute("href", "/library/library-item-1");
		});
	});

	it("shows Add to Collection on search result cards", async () => {
		const user = userEvent.setup();
		vi.spyOn(searchService, "searchLibrary").mockResolvedValue([
			{
				id: "library-item-1",
				contentId: "content-1",
				artifactId: "artifact-1",
				type: "summary",
				title: "The Roman Empire",
				createdAt: "2026-06-26T12:00:00+00:00",
			},
		]);
		vi.spyOn(collectionService, "listCollections").mockResolvedValue([
			{
				id: "collection-1",
				name: "Ancient Rome",
				description: "Resources about Roman history",
				createdAt: "2026-06-27T12:00:00+00:00",
			},
		]);

		renderWithProviders(
			<MemoryRouter>
				<LibraryPage />
			</MemoryRouter>,
		);

		await waitFor(() => {
			expect(screen.getByText("The Roman Empire")).toBeInTheDocument();
		});

		await user.type(
			screen.getByRole("searchbox", { name: "Search library" }),
			"Roman",
		);

		await waitFor(() => {
			expect(
				screen.getByRole("button", { name: "Add to Collection" }),
			).toBeInTheDocument();
		});

		await user.click(screen.getByRole("button", { name: "Add to Collection" }));

		expect(
			screen.getByRole("dialog", { name: "Assign to collection" }),
		).toBeInTheDocument();
	});

	it("displays library items from the service layer", async () => {
		renderWithProviders(
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
		expect(screen.getByText("Ancient Rome Events")).toBeInTheDocument();
		expect(screen.getByText("Summary")).toBeInTheDocument();
		expect(screen.getByText("Quiz")).toBeInTheDocument();
		expect(screen.getByText("Flashcards")).toBeInTheDocument();
		expect(screen.getByText("Timeline")).toBeInTheDocument();
	});

	it("shows EmptyState when the library is empty", async () => {
		vi.spyOn(libraryService, "listItems").mockResolvedValue([]);

		renderWithProviders(
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

		renderWithProviders(
			<MemoryRouter>
				<LibraryPage />
			</MemoryRouter>,
		);

		await waitFor(() => {
			expect(screen.getByText("Unable to load library")).toBeInTheDocument();
		});
	});

	it("links library cards to the item details page", async () => {
		renderWithProviders(
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
		renderWithProviders(
			<MemoryRouter>
				<LibraryPage />
			</MemoryRouter>,
		);

		await waitFor(() => {
			expect(screen.getByText("The Roman Empire")).toBeInTheDocument();
		});

		expect(
			screen.getAllByRole("button", { name: "Add to Collection" }),
		).toHaveLength(4);
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

		renderWithProviders(
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

		renderWithProviders(
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

		renderWithProviders(
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

		renderWithProviders(
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
