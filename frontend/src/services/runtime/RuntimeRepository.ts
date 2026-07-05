import type {
	RuntimeCatalog,
	RuntimeEngine,
	RuntimeHealth,
	RuntimeOverview,
	RuntimeProfile,
	RuntimeReadiness,
	RuntimeRecommendation,
	RuntimeValidationReport,
} from "./types";

export interface RuntimeRepository {
	getOverview(): Promise<RuntimeOverview>;
	getReadiness(): Promise<RuntimeReadiness>;
	getHealth(): Promise<RuntimeHealth>;
	listEngines(): Promise<RuntimeEngine[]>;
	getCatalog(): Promise<RuntimeCatalog>;
	getRecommendations(): Promise<RuntimeRecommendation[]>;
	listProfiles(): Promise<RuntimeProfile[]>;
	testEngine(engineId: string): Promise<Record<string, unknown>>;
	runFullBenchmark(): Promise<Record<string, unknown>>;
	validatePipeline(): Promise<RuntimeValidationReport>;
}
