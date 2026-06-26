import { contentMock } from "@/mock/content";
import type { ContentRepository } from "./ContentRepository";
import type { Content, CreateContentInput, CreateContentResult } from "./types";

export class MockContentRepository implements ContentRepository {
	async listContents(): Promise<Content[]> {
		return contentMock.contents.map((item) => ({ ...item }));
	}

	async createContent(input: CreateContentInput): Promise<CreateContentResult> {
		const id = String(contentMock.contents.length + 1);
		contentMock.contents.push({
			id,
			title: input.title,
			sourceType: input.sourceType,
			status: "processing",
			progress: 0,
		});
		return { id };
	}
}

export class EmptyMockContentRepository implements ContentRepository {
	async listContents(): Promise<Content[]> {
		return [];
	}

	async createContent(
		_input: CreateContentInput,
	): Promise<CreateContentResult> {
		return { id: "1" };
	}
}
