import { fireEvent, render, screen, waitFor } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { MemoryRouter } from "react-router";
import { afterEach, describe, expect, it, vi } from "vitest";
import { ImportPage } from "@/pages/Import/ImportPage";
import { contentService } from "@/services/content/ContentService";

describe("ImportPage — S2-SLICE-04 real content creation", () => {
	afterEach(() => {
		vi.restoreAllMocks();
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

	it("shows success with content id after PDF import", async () => {
		vi.spyOn(contentService, "simulateUpload").mockResolvedValue();
		vi.spyOn(contentService, "importPdf").mockResolvedValue({
			id: "test-content-id",
		});
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
			expect(screen.getByText("Upload complete")).toBeInTheDocument();
		});

		expect(screen.getByText("test-content-id")).toBeInTheDocument();
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

		expect(screen.getByText(/Could not create content/i)).toBeInTheDocument();
	});
});
