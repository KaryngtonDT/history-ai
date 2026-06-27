import { render, screen, waitFor } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { MemoryRouter } from "react-router";
import { afterEach, describe, expect, it, vi } from "vitest";
import { CollectionsPage } from "@/pages/Collections/CollectionsPage";
import { collectionService } from "@/services/collection/CollectionService";
import type { Collection } from "@/services/collection/types";

const mockCollections: Collection[] = [
	{
		id: "collection-1",
		name: "Ancient Rome",
		description: "Resources about Roman history",
		createdAt: "2026-06-27T12:00:00+00:00",
	},
	{
		id: "collection-2",
		name: "Philosophy",
		description: "Philosophy resources",
		createdAt: "2026-06-27T11:00:00+00:00",
	},
];

describe("CollectionsPage", () => {
	afterEach(() => {
		vi.restoreAllMocks();
	});

	it("shows loading state while collections are fetched", () => {
		vi.spyOn(collectionService, "listCollections").mockReturnValue(
			new Promise(() => {}),
		);

		render(
			<MemoryRouter>
				<CollectionsPage />
			</MemoryRouter>,
		);

		expect(
			screen.getByRole("status", { name: "Loading collections" }),
		).toBeInTheDocument();
	});

	it("displays collections from the service layer", async () => {
		vi.spyOn(collectionService, "listCollections").mockResolvedValue(
			mockCollections,
		);

		render(
			<MemoryRouter>
				<CollectionsPage />
			</MemoryRouter>,
		);

		expect(
			screen.getByRole("heading", { name: "Collections" }),
		).toBeInTheDocument();

		await waitFor(() => {
			expect(screen.getByText("Ancient Rome")).toBeInTheDocument();
		});

		expect(screen.getByText("Philosophy")).toBeInTheDocument();
		expect(
			screen.getByText("Resources about Roman history"),
		).toBeInTheDocument();
	});

	it("shows EmptyState when there are no collections", async () => {
		vi.spyOn(collectionService, "listCollections").mockResolvedValue([]);

		render(
			<MemoryRouter>
				<CollectionsPage />
			</MemoryRouter>,
		);

		await waitFor(() => {
			expect(screen.getByText("No collections yet")).toBeInTheDocument();
		});
	});

	it("shows EmptyState when loading fails", async () => {
		vi.spyOn(collectionService, "listCollections").mockRejectedValue(
			new Error("network"),
		);

		render(
			<MemoryRouter>
				<CollectionsPage />
			</MemoryRouter>,
		);

		await waitFor(() => {
			expect(
				screen.getByText("Unable to load collections"),
			).toBeInTheDocument();
		});
	});
});

describe("CreateCollectionDialog", () => {
	afterEach(() => {
		vi.restoreAllMocks();
	});

	it("creates a collection and reloads the list", async () => {
		const user = userEvent.setup();
		const listCollections = vi
			.spyOn(collectionService, "listCollections")
			.mockResolvedValueOnce(mockCollections)
			.mockResolvedValueOnce([
				{
					id: "collection-3",
					name: "Medieval Europe",
					description: "Knights and castles",
					createdAt: "2026-06-27T13:00:00+00:00",
				},
				...mockCollections,
			]);
		const createCollection = vi
			.spyOn(collectionService, "createCollection")
			.mockResolvedValue({
				id: "collection-3",
				name: "Medieval Europe",
				description: "Knights and castles",
				createdAt: "2026-06-27T13:00:00+00:00",
			});

		render(
			<MemoryRouter>
				<CollectionsPage />
			</MemoryRouter>,
		);

		await waitFor(() => {
			expect(screen.getByText("Ancient Rome")).toBeInTheDocument();
		});

		await user.click(screen.getByRole("button", { name: "Create collection" }));
		await user.type(
			screen.getByRole("textbox", { name: "Name" }),
			"Medieval Europe",
		);
		await user.type(
			screen.getByRole("textbox", { name: "Description" }),
			"Knights and castles",
		);
		await user.click(screen.getByRole("button", { name: "Create" }));

		await waitFor(() => {
			expect(createCollection).toHaveBeenCalledWith({
				name: "Medieval Europe",
				description: "Knights and castles",
			});
		});

		await waitFor(() => {
			expect(listCollections).toHaveBeenCalledTimes(2);
			expect(screen.getByText("Medieval Europe")).toBeInTheDocument();
		});
	});

	it("shows an error when creation fails", async () => {
		const user = userEvent.setup();
		vi.spyOn(collectionService, "listCollections").mockResolvedValue(
			mockCollections,
		);
		vi.spyOn(collectionService, "createCollection").mockRejectedValue(
			new Error("failed"),
		);

		render(
			<MemoryRouter>
				<CollectionsPage />
			</MemoryRouter>,
		);

		await waitFor(() => {
			expect(screen.getByText("Ancient Rome")).toBeInTheDocument();
		});

		await user.click(screen.getByRole("button", { name: "Create collection" }));
		await user.type(screen.getByRole("textbox", { name: "Name" }), "New Topic");
		await user.click(screen.getByRole("button", { name: "Create" }));

		await waitFor(() => {
			expect(
				screen.getByText("Could not create the collection. Please try again."),
			).toBeInTheDocument();
		});
	});
});

describe("AssignToCollectionDialog", () => {
	afterEach(() => {
		vi.restoreAllMocks();
	});

	it("assigns a library item successfully", async () => {
		const user = userEvent.setup();
		const listCollections = vi
			.spyOn(collectionService, "listCollections")
			.mockResolvedValue(mockCollections);
		const assignLibraryItem = vi
			.spyOn(collectionService, "assignLibraryItem")
			.mockResolvedValue({
				id: "collection-item-2",
				collectionId: "collection-1",
				libraryItemId: "library-item-2",
				createdAt: "2026-06-27T13:00:00+00:00",
			});

		const { AssignToCollectionDialog } = await import(
			"@/features/collection/AssignToCollectionDialog"
		);
		const onClose = vi.fn();

		render(
			<AssignToCollectionDialog
				open
				onClose={onClose}
				libraryItemId="library-item-2"
			/>,
		);

		await waitFor(() => {
			expect(listCollections).toHaveBeenCalled();
		});

		await user.click(screen.getByRole("button", { name: "Assign" }));

		await waitFor(() => {
			expect(assignLibraryItem).toHaveBeenCalledWith(
				"collection-1",
				"library-item-2",
			);
			expect(
				screen.getByText("Library item assigned successfully."),
			).toBeInTheDocument();
		});
	});

	it("shows duplicate state when assignment conflicts", async () => {
		const user = userEvent.setup();
		vi.spyOn(collectionService, "listCollections").mockResolvedValue(
			mockCollections,
		);
		const { CollectionAssignmentConflictError } = await import(
			"@/services/collection/MockCollectionRepository"
		);
		vi.spyOn(collectionService, "assignLibraryItem").mockRejectedValue(
			new CollectionAssignmentConflictError(),
		);

		const { AssignToCollectionDialog } = await import(
			"@/features/collection/AssignToCollectionDialog"
		);

		render(
			<AssignToCollectionDialog
				open
				onClose={vi.fn()}
				libraryItemId="library-item-1"
			/>,
		);

		await waitFor(() => {
			expect(
				screen.getByRole("combobox", { name: "Collection" }),
			).toBeInTheDocument();
		});

		await user.click(screen.getByRole("button", { name: "Assign" }));

		await waitFor(() => {
			expect(
				screen.getByText(
					"This library item is already in the selected collection.",
				),
			).toBeInTheDocument();
		});
	});

	it("shows an error when assignment fails", async () => {
		const user = userEvent.setup();
		vi.spyOn(collectionService, "listCollections").mockResolvedValue(
			mockCollections,
		);
		vi.spyOn(collectionService, "assignLibraryItem").mockRejectedValue(
			new Error("failed"),
		);

		const { AssignToCollectionDialog } = await import(
			"@/features/collection/AssignToCollectionDialog"
		);

		render(
			<AssignToCollectionDialog
				open
				onClose={vi.fn()}
				libraryItemId="library-item-2"
			/>,
		);

		await waitFor(() => {
			expect(
				screen.getByRole("combobox", { name: "Collection" }),
			).toBeInTheDocument();
		});

		await user.click(screen.getByRole("button", { name: "Assign" }));

		await waitFor(() => {
			expect(
				screen.getByText(
					"Could not assign the library item. Please try again.",
				),
			).toBeInTheDocument();
		});
	});
});
