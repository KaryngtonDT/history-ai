import { fireEvent, render, screen, waitFor } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { describe, expect, it, vi } from "vitest";
import { ImportPage } from "@/pages/Import/ImportPage";

describe("ImportPage — S1-SLICE-05 simulated upload", () => {
	it("rejects non-PDF files", () => {
		render(<ImportPage />);

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

	it("simulates PDF upload through success state", async () => {
		vi.useFakeTimers({ shouldAdvanceTime: true });
		const user = userEvent.setup();

		render(<ImportPage />);

		const input = document.querySelector(
			'input[type="file"]',
		) as HTMLInputElement;
		const pdfFile = new File(["pdf"], "roman-empire.pdf", {
			type: "application/pdf",
		});

		await user.upload(input, pdfFile);

		expect(screen.getByText("Uploading")).toBeInTheDocument();
		expect(screen.getByText("roman-empire.pdf")).toBeInTheDocument();

		await vi.runAllTimersAsync();

		await waitFor(() => {
			expect(screen.getByText("Upload complete")).toBeInTheDocument();
		});

		expect(screen.getByText("Completed")).toBeInTheDocument();

		vi.useRealTimers();
	});
});
