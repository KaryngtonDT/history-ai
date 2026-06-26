import { describe, expect, it } from "vitest";
import { libraryEmptyMock } from "@/mock/library";
import type { LibraryRepository } from "./LibraryRepository";
import { LibraryService } from "./LibraryService";
import { MockLibraryRepository } from "./MockLibraryRepository";

describe("LibraryService", () => {
	it("returns library contents from the mock repository", () => {
		const service = new LibraryService(new MockLibraryRepository());
		const data = service.getLibrary();

		expect(data.contents).toHaveLength(3);
		expect(data.contents[0]?.title).toBe("The Roman Empire");
	});

	it("supports an empty library through the repository", () => {
		const emptyRepository: LibraryRepository = {
			getLibrary: () => libraryEmptyMock,
		};
		const service = new LibraryService(emptyRepository);
		const data = service.getLibrary();

		expect(data.contents).toHaveLength(0);
	});
});
