import type {
	RuntimeCapabilityMaturityOverview,
	RuntimeCatalog,
	RuntimeCompatibilitySummary,
	RuntimeEngine,
	RuntimeEngineCompatibility,
	RuntimeHardwareOverview,
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
	provisionCompatibleAll(): Promise<Record<string, unknown>>;
	getProvisioningPlan(): Promise<Record<string, unknown>>;
	runFullBenchmark(): Promise<Record<string, unknown>>;
	validatePipeline(): Promise<RuntimeValidationReport>;
	getHardware(): Promise<RuntimeHardwareOverview>;
	getCompatibility(): Promise<RuntimeCompatibilitySummary>;
	getCapabilityMaturity(): Promise<RuntimeCapabilityMaturityOverview>;
	getEngineCompatibility(engineId: string): Promise<RuntimeEngineCompatibility>;
}
