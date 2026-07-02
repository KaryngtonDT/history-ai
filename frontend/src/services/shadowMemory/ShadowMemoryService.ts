import type { ShadowMemoryRepository } from "./ShadowMemoryRepository";
import { createShadowMemoryRepository } from "./ShadowMemoryRepositoryFactory";
import type { MemorySearchRequest } from "./types";

export class ShadowMemoryService {
	private readonly repository: ShadowMemoryRepository;

	constructor(
		repository: ShadowMemoryRepository = createShadowMemoryRepository(),
	) {
		this.repository = repository;
	}

	getTimeline(scopeKey?: string) {
		return this.repository.getTimeline(scopeKey);
	}

	getConcepts(scopeKey?: string) {
		return this.repository.getConcepts(scopeKey);
	}

	getVocabulary(scopeKey?: string) {
		return this.repository.getVocabulary(scopeKey);
	}

	getMilestones(scopeKey?: string) {
		return this.repository.getMilestones(scopeKey);
	}

	getConnections(scopeKey?: string) {
		return this.repository.getConnections(scopeKey);
	}

	getJourney(scopeKey?: string) {
		return this.repository.getJourney(scopeKey);
	}

	search(request: MemorySearchRequest) {
		return this.repository.search(request);
	}

	reset(scopeKey?: string) {
		return this.repository.reset(scopeKey);
	}
}

export const shadowMemoryService = new ShadowMemoryService();
