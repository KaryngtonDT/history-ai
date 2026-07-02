import type { ShadowBrainRepository } from "./ShadowBrainRepository";
import { createShadowBrainRepository } from "./ShadowBrainRepositoryFactory";
import type {
	AddBookmarkRequest,
	AddNoteRequest,
	RebuildWorkspaceRequest,
} from "./types";

export class ShadowBrainService {
	private readonly repository: ShadowBrainRepository;

	constructor(
		repository: ShadowBrainRepository = createShadowBrainRepository(),
	) {
		this.repository = repository;
	}

	getDashboard(scopeKey?: string) {
		return this.repository.getDashboard(scopeKey);
	}

	getConceptTree(scopeKey?: string) {
		return this.repository.getConceptTree(scopeKey);
	}

	getConcept(id: string, scopeKey?: string) {
		return this.repository.getConcept(id, scopeKey);
	}

	search(query: string, scopeKey?: string) {
		return this.repository.search(query, scopeKey);
	}

	getTimeline(from?: string, to?: string, scopeKey?: string) {
		return this.repository.getTimeline(from, to, scopeKey);
	}

	getDiff(resourceType: string, resourceId: string, scopeKey?: string) {
		return this.repository.getDiff(resourceType, resourceId, scopeKey);
	}

	addBookmark(request: AddBookmarkRequest) {
		return this.repository.addBookmark(request);
	}

	addNote(request: AddNoteRequest) {
		return this.repository.addNote(request);
	}

	removeBookmark(id: string, scopeKey?: string) {
		return this.repository.removeBookmark(id, scopeKey);
	}

	rebuild(request?: RebuildWorkspaceRequest) {
		return this.repository.rebuild(request);
	}
}

export const shadowBrainService = new ShadowBrainService();
