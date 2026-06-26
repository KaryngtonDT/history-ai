import { fireEvent, render, screen, waitFor } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { MemoryRouter, Route, Routes } from "react-router";
import { describe, expect, it, vi } from "vitest";
import { ImportPage } from "@/pages/Import/ImportPage";
import { ProcessingPage } from "@/pages/Processing/ProcessingPage";

describe("ImportPage — S1-SLICE-05 simulated upload", () => {
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

	it("navigates to processing after simulated PDF upload", async () => {
		vi.useFakeTimers({ shouldAdvanceTime: true });
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

		expect(screen.getByText("Uploading")).toBeInTheDocument();

		await vi.runAllTimersAsync();

		await waitFor(() => {
			expect(
				screen.getByRole("heading", { name: "Processing" }),
			).toBeInTheDocument();
		});

		vi.useRealTimers();
	});
});
