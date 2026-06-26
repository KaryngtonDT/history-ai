import { describe, expect, it } from "vitest";
import { deriveTitleFromPdfFileName } from "./deriveTitleFromPdfFileName";

describe("deriveTitleFromPdfFileName", () => {
	it("removes the .pdf extension", () => {
		expect(deriveTitleFromPdfFileName("Roman Empire.pdf")).toBe("Roman Empire");
	});

	it("handles uppercase extension", () => {
		expect(deriveTitleFromPdfFileName("notes.PDF")).toBe("notes");
	});

	it("falls back to the original name when empty", () => {
		expect(deriveTitleFromPdfFileName(".pdf")).toBe(".pdf");
	});
});
