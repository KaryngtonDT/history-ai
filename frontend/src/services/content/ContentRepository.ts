import type {
	Content,
	CreateContentInput,
	CreateContentResult,
} from "./domain/Content";

export interface ContentRepository {
	listContents(): Promise<Content[]>;
	createContent(input: CreateContentInput): Promise<CreateContentResult>;
}
