import type { RuntimeRepository } from "./RuntimeRepository";
import { createRuntimeRepository } from "./RuntimeRepositoryFactory";

export class RuntimeService {
	constructor(private readonly repository: RuntimeRepository) {}

	getOverview() {
		return this.repository.getOverview();
	}

	getReadiness() {
		return this.repository.getReadiness();
	}

	getHealth() {
		return this.repository.getHealth();
	}

	listEngines() {
		return this.repository.listEngines();
	}

	getCatalog() {
		return this.repository.getCatalog();
	}

	getRecommendations() {
		return this.repository.getRecommendations();
	}

	listProfiles() {
		return this.repository.listProfiles();
	}

	testEngine(engineId: string) {
		return this.repository.testEngine(engineId);
	}

	runFullBenchmark() {
		return this.repository.runFullBenchmark();
	}

	validatePipeline() {
		return this.repository.validatePipeline();
	}
}

export const runtimeService = new RuntimeService(createRuntimeRepository());
