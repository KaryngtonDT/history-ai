import { screen, waitFor } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { MemoryRouter, Route, Routes } from "react-router";
import { afterEach, describe, expect, it, vi } from "vitest";
import { HomeMissionControl } from "@/features/home";
import { ImportPage } from "@/pages/Import/ImportPage";
import { VideoUploadPage } from "@/pages/VideoUpload/VideoUploadPage";
import { workItemService } from "@/services/workItem/WorkItemService";
import { renderWithProviders } from "@/test/render";

describe("HomeMissionControl", () => {
	afterEach(() => {
		vi.restoreAllMocks();
	});

	it("renders create section and recent work", async () => {
		renderWithProviders(
			<MemoryRouter>
				<HomeMissionControl />
			</MemoryRouter>,
		);

		await waitFor(() => {
			expect(
				screen.getByText("What do you want to transform?"),
			).toBeInTheDocument();
		});

		expect(screen.getByText("Recent work")).toBeInTheDocument();
		expect(screen.getByText("At a glance")).toBeInTheDocument();
		expect(screen.getByText("AI Director")).toBeInTheDocument();
	});

	it("navigates from create video card to upload page", async () => {
		const user = userEvent.setup();

		renderWithProviders(
			<MemoryRouter initialEntries={["/"]}>
				<Routes>
					<Route path="/" element={<HomeMissionControl />} />
					<Route path="/video/upload" element={<VideoUploadPage />} />
				</Routes>
			</MemoryRouter>,
		);

		await waitFor(() => {
			expect(
				screen.getByRole("link", { name: "Create Video" }),
			).toBeInTheDocument();
		});

		await user.click(screen.getByRole("link", { name: "Create Video" }));
		expect(
			screen.getByRole("heading", { name: "Upload Video", level: 1 }),
		).toBeInTheDocument();
	});

	it("shows empty recent work when summary fails", async () => {
		vi.spyOn(workItemService, "getSummary").mockRejectedValue(
			new Error("network"),
		);

		renderWithProviders(
			<MemoryRouter>
				<HomeMissionControl />
			</MemoryRouter>,
		);

		await waitFor(() => {
			expect(screen.getByText("Unable to load home")).toBeInTheDocument();
		});
	});
});

describe("HomeMissionControl — import navigation", () => {
	it("navigates PDF card to import", async () => {
		const user = userEvent.setup();

		renderWithProviders(
			<MemoryRouter initialEntries={["/"]}>
				<Routes>
					<Route path="/" element={<HomeMissionControl />} />
					<Route path="/import" element={<ImportPage />} />
				</Routes>
			</MemoryRouter>,
		);

		await waitFor(() => {
			expect(
				screen.getByRole("link", { name: "Create PDF" }),
			).toBeInTheDocument();
		});

		await user.click(screen.getByRole("link", { name: "Create PDF" }));
		expect(screen.getByRole("heading", { name: "Import" })).toBeInTheDocument();
	});
});
