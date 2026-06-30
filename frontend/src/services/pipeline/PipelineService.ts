import type { PipelineRepository } from "./PipelineRepository";
import { createPipelineRepository } from "./PipelineRepositoryFactory";
import type { PipelineConfiguration, PipelineStage } from "./types";

export class PipelineService {
	private readonly repository: PipelineRepository;

	constructor(repository: PipelineRepository) {
		this.repository = repository;
	}

	loadConfiguration(): Promise<PipelineConfiguration> {
		return this.repository.loadConfiguration();
	}

	saveConfiguration(stages: PipelineStage[]): Promise<PipelineConfiguration> {
		if (stages.length === 0) {
			return Promise.reject(
				new Error("Pipeline must include at least one stage"),
			);
		}

		return this.repository.saveConfiguration(stages);
	}

	resetConfiguration(): Promise<PipelineConfiguration> {
		return this.repository.resetConfiguration();
	}
}

export const pipelineService = new PipelineService(createPipelineRepository());
