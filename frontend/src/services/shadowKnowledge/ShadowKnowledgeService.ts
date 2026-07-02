import type { ShadowKnowledgeRepository } from "./ShadowKnowledgeRepository";
import { createShadowKnowledgeRepository } from "./ShadowKnowledgeRepositoryFactory";
import type { KnowledgeSearchRequest } from "./types";

export class ShadowKnowledgeService {
	private readonly repository: ShadowKnowledgeRepository;

	constructor(
		repository: ShadowKnowledgeRepository = createShadowKnowledgeRepository(),
	) {
		this.repository = repository;
	}

	getGraph(scopeKey?: string) {
		return this.repository.getGraph(scopeKey);
	}

	getNode(id: string, scopeKey?: string) {
		return this.repository.getNode(id, scopeKey);
	}

	getPath(scopeKey?: string) {
		return this.repository.getPath(scopeKey);
	}

	getGaps(goalKey?: string, scopeKey?: string) {
		return this.repository.getGaps(goalKey, scopeKey);
	}

	getRelated(key: string, scopeKey?: string) {
		return this.repository.getRelated(key, scopeKey);
	}

	search(request: KnowledgeSearchRequest) {
		return this.repository.search(request);
	}

	rebuild(scopeKey?: string) {
		return this.repository.rebuild(scopeKey);
	}

	reset(scopeKey?: string) {
		return this.repository.reset(scopeKey);
	}
}

export const shadowKnowledgeService = new ShadowKnowledgeService();
