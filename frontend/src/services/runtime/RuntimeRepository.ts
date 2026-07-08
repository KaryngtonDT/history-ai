import type { RuntimeEngineAnalytics } from "./analyticsTypes";
import type {
	RuntimeEngineManagement,
	RuntimeSelectionUpdate,
} from "./managementTypes";
import type {
	RuntimeCapabilityMaturityOverview,
	RuntimeCapabilitySelectionView,
	RuntimeCatalog,
	RuntimeCompatibilitySummary,
	RuntimeDashboard,
	RuntimeEngine,
	RuntimeEngineCompatibility,
	RuntimeEngineTestResult,
	RuntimeHardwareOverview,
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
	getDashboard(): Promise<RuntimeDashboard>;
	getEngineCompatibility(engineId: string): Promise<RuntimeEngineCompatibility>;
	listEngineAnalytics(): Promise<RuntimeEngineAnalytics[]>;
	getCapabilitySelectionView(
		capability: string,
	): Promise<RuntimeCapabilitySelectionView>;
	getSelection(): Promise<Record<string, unknown>>;
	getEngineManagement(): Promise<RuntimeEngineManagement>;
	updateSelection(
		payload: RuntimeSelectionUpdate,
	): Promise<Record<string, unknown>>;
	installEngine(engineId: string): Promise<Record<string, unknown>>;
	updateEngine(engineId: string): Promise<Record<string, unknown>>;
	repairEngine(engineId: string): Promise<Record<string, unknown>>;
	removeEngine(engineId: string): Promise<void>;
	validateEngine(engineId: string): Promise<Record<string, unknown>>;
	listExecutions(): Promise<Array<Record<string, unknown>>>;
	getRecommendationProfiles(): Promise<Record<string, unknown>>;
}
