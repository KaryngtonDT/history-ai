import type { RuntimeRepository } from "./RuntimeRepository";
import { createRuntimeRepository } from "./RuntimeRepositoryFactory";

export class RuntimeService {
	private readonly repository: RuntimeRepository;

	constructor(repository: RuntimeRepository) {
		this.repository = repository;
	}

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

	provisionEngine(engineId: string) {
		return this.repository.provisionEngine(engineId);
	}

	provisionAll() {
		return this.repository.provisionAll();
	}

	provisionCompatibleAll() {
		return this.repository.provisionCompatibleAll();
	}

	getProvisioningPlan() {
		return this.repository.getProvisioningPlan();
	}

	runFullBenchmark() {
		return this.repository.runFullBenchmark();
	}

	validatePipeline() {
		return this.repository.validatePipeline();
	}

	getHardware() {
		return this.repository.getHardware();
	}

	getCompatibility() {
		return this.repository.getCompatibility();
	}

	getCapabilityMaturity() {
		return this.repository.getCapabilityMaturity();
	}

	getDashboard() {
		return this.repository.getDashboard();
	}

	getEngineCompatibility(engineId: string) {
		return this.repository.getEngineCompatibility(engineId);
	}

	listEngineAnalytics() {
		return this.repository.listEngineAnalytics();
	}

	getCapabilitySelectionView(capability: string) {
		return this.repository.getCapabilitySelectionView(capability);
	}

	getSelection() {
		return this.repository.getSelection();
	}
}

export const runtimeService = new RuntimeService(createRuntimeRepository());
