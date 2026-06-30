import type { AIEngineRepository } from "./AIEngineRepository";
import { createAIEngineRepository } from "./AIEngineRepositoryFactory";
import type { AIEngine } from "./types";

export class AIEngineService {
	private readonly repository: AIEngineRepository;

	constructor(repository: AIEngineRepository) {
		this.repository = repository;
	}

	listEngines(): Promise<AIEngine[]> {
		return this.repository.listEngines();
	}
}

export const aiEngineService = new AIEngineService(createAIEngineRepository());
