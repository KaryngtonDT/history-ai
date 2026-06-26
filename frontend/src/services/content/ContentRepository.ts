import type { Content, CreateContentInput, CreateContentResult } from "./types";

export interface ContentRepository {
	listContents(): Promise<Content[]>;
	createContent(input: CreateContentInput): Promise<CreateContentResult>;
}
