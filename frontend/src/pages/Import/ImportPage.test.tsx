import { fireEvent, render, screen, waitFor } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { MemoryRouter, Route, Routes } from "react-router";
import { afterEach, describe, expect, it, vi } from "vitest";
import { ImportPage } from "@/pages/Import/ImportPage";
import { ProcessingPage } from "@/pages/Processing/ProcessingPage";
import { contentService } from "@/services/content/ContentService";
import { processingService } from "@/services/processing/ProcessingService";

describe("ImportPage — S4-SLICE-04 end-to-end processing flow", () => {
	afterEach(() => {
		vi.restoreAllMocks();
		vi.useRealTimers();
	});

	it("rejects non-PDF files", () => {
		render(
			<MemoryRouter>
				<ImportPage />
			</MemoryRouter>,
		);

		const input = document.querySelector(
			'input[type="file"]',
		) as HTMLInputElement;
		const textFile = new File(["notes"], "notes.txt", { type: "text/plain" });

		fireEvent.change(input, { target: { files: [textFile] } });

		expect(screen.getByText("Upload failed")).toBeInTheDocument();
		expect(
			screen.getByText("Only PDF files are supported."),
		).toBeInTheDocument();
	});

	it("creates content and processing job then navigates to processing", async () => {
		vi.useFakeTimers({ shouldAdvanceTime: true });
		vi.spyOn(contentService, "simulateUpload").mockResolvedValue();
		vi.spyOn(contentService, "importPdf").mockResolvedValue({
			id: "test-content-id",
		});
		vi.spyOn(processingService, "createProcessingJob").mockResolvedValue({
			id: "1",
			status: "pending",
			progress: 0,
		});

		const user = userEvent.setup();

		render(
			<MemoryRouter initialEntries={["/import"]}>
				<Routes>
					<Route path="/import" element={<ImportPage />} />
					<Route path="/processing/:id" element={<ProcessingPage />} />
				</Routes>
			</MemoryRouter>,
		);

		const input = document.querySelector(
			'input[type="file"]',
		) as HTMLInputElement;
		const pdfFile = new File(["pdf"], "roman-empire.pdf", {
			type: "application/pdf",
		});

		await user.upload(input, pdfFile);

		await waitFor(() => {
			expect(processingService.createProcessingJob).toHaveBeenCalledWith(
				"test-content-id",
				"summary",
			);
		});

		await waitFor(() => {
			expect(
				screen.getByRole("heading", { name: "Processing" }),
			).toBeInTheDocument();
		});

		expect(screen.getByText("The Roman Empire")).toBeInTheDocument();

		await vi.runAllTimersAsync();

		await waitFor(() => {
			expect(screen.getByText("Processing complete")).toBeInTheDocument();
		});
	});

	it("shows error when content creation fails", async () => {
		vi.spyOn(contentService, "simulateUpload").mockResolvedValue();
		vi.spyOn(contentService, "importPdf").mockRejectedValue(
			new Error("network"),
		);
		const user = userEvent.setup();

		render(
			<MemoryRouter>
				<ImportPage />
			</MemoryRouter>,
		);

		const input = document.querySelector(
			'input[type="file"]',
		) as HTMLInputElement;
		const pdfFile = new File(["pdf"], "roman-empire.pdf", {
			type: "application/pdf",
		});

		await user.upload(input, pdfFile);

		await waitFor(() => {
			expect(screen.getByText("Upload failed")).toBeInTheDocument();
		});

		expect(screen.getByText(/Could not start processing/i)).toBeInTheDocument();
	});
});
