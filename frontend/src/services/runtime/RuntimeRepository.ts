import type {
	RuntimeCatalog,
	RuntimeEngine,
	RuntimeHealth,
	RuntimeOverview,
	RuntimeProfile,
	RuntimeReadiness,
	RuntimeRecommendation,
	RuntimeValidationReport,
	RuntimeEngineTestResult,
} from "./types";

export interface RuntimeRepository {
	getOverview(): Promise<RuntimeOverview>;
	getReadiness(): Promise<RuntimeReadiness>;
	getHealth(): Promise<RuntimeHealth>;
	listEngines(): Promise<RuntimeEngine[]>;
	getCatalog(): Promise<RuntimeCatalog>;
	getRecommendations(): Promise<RuntimeRecommendation[]>;
	listProfiles(): Promise<RuntimeProfile[]>;
	testEngine(engineId: string): Promise<RuntimeEngineTestResult>;
	provisionEngine(engineId: string): Promise<Record<string, unknown>>;
	provisionAll(): Promise<Record<string, unknown>>;
	runFullBenchmark(): Promise<Record<string, unknown>>;
	validatePipeline(): Promise<RuntimeValidationReport>;
}
