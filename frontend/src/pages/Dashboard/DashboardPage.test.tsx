import { render, screen, waitFor } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { MemoryRouter, Route, Routes } from "react-router";
import { afterEach, describe, expect, it, vi } from "vitest";
import { DashboardPage } from "@/pages/Dashboard/DashboardPage";
import { ImportPage } from "@/pages/Import/ImportPage";
import { VideoUploadPage } from "@/pages/VideoUpload/VideoUploadPage";
import { contentService } from "@/services/content/ContentService";

describe("DashboardPage — S2-SLICE-05 real backend data", () => {
	afterEach(() => {
		vi.restoreAllMocks();
	});

	it("renders dashboard data from the service layer", async () => {
		render(
			<MemoryRouter>
				<DashboardPage />
			</MemoryRouter>,
		);

		await waitFor(() => {
			expect(screen.getByText("The Roman Empire")).toBeInTheDocument();
		});

		expect(
			screen.getByText("Transform knowledge into understanding."),
		).toBeInTheDocument();
		expect(screen.getByRole("progressbar")).toHaveAttribute(
			"aria-valuenow",
			"62",
		);
		expect(screen.getByText("French Revolution")).toBeInTheDocument();
		expect(screen.getByText("Contents")).toBeInTheDocument();
		expect(screen.getByText("12")).toBeInTheDocument();
	});

	it("shows EmptyState when there is no content", async () => {
		vi.spyOn(contentService, "getDashboardData").mockResolvedValue({
			recentContents: [],
			statistics: {
				contents: 0,
				completed: 0,
				processing: 0,
				artifacts: 0,
			},
		});

		render(
			<MemoryRouter>
				<DashboardPage />
			</MemoryRouter>,
		);

		await waitFor(() => {
			expect(screen.getByText("No content yet")).toBeInTheDocument();
		});

		expect(screen.getByText("Contents")).toBeInTheDocument();
		expect(screen.getAllByText("0")).toHaveLength(4);
	});

	it("shows EmptyState when the backend is unavailable", async () => {
		vi.spyOn(contentService, "getDashboardData").mockRejectedValue(
			new Error("network"),
		);

		render(
			<MemoryRouter>
				<DashboardPage />
			</MemoryRouter>,
		);

		await waitFor(() => {
			expect(screen.getByText("Unable to load dashboard")).toBeInTheDocument();
		});
	});

	it("logs content route on card click", async () => {
		const user = userEvent.setup();
		const logSpy = vi.spyOn(console, "log").mockImplementation(() => {});

		render(
			<MemoryRouter>
				<DashboardPage />
			</MemoryRouter>,
		);

		await waitFor(() => {
			expect(
				screen.getByRole("button", { name: /The Roman Empire/i }),
			).toBeInTheDocument();
		});

		await user.click(screen.getByRole("button", { name: /The Roman Empire/i }));
		expect(logSpy).toHaveBeenCalledWith("/content/1");

		logSpy.mockRestore();
	});

	it("navigates quick actions to import and video upload", async () => {
		const user = userEvent.setup();

		render(
			<MemoryRouter initialEntries={["/"]}>
				<Routes>
					<Route path="/" element={<DashboardPage />} />
					<Route path="/import" element={<ImportPage />} />
					<Route path="/video/upload" element={<VideoUploadPage />} />
				</Routes>
			</MemoryRouter>,
		);

		await waitFor(() => {
			expect(
				screen.getByRole("button", { name: "Import Video" }),
			).toBeInTheDocument();
		});

		await user.click(screen.getByRole("button", { name: "Import Video" }));
		expect(
			screen.getByRole("heading", { name: "Upload Video" }),
		).toBeInTheDocument();
	});
});
