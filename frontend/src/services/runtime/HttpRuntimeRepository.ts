import {
	RUNTIME_ANALYTICS_ENGINES_PATH,
	RUNTIME_BENCHMARK_FULL_PATH,
	RUNTIME_CAPABILITY_MATURITY_PATH,
	RUNTIME_CATALOG_PATH,
	RUNTIME_COMPATIBILITY_PATH,
	RUNTIME_DASHBOARD_PATH,
	RUNTIME_ENGINE_MANAGEMENT_PATH,
	RUNTIME_ENGINES_PATH,
	RUNTIME_EXECUTIONS_PATH,
	RUNTIME_HARDWARE_PATH,
	RUNTIME_HEALTH_PATH,
	RUNTIME_PATH,
	RUNTIME_PIPELINE_VALIDATE_PATH,
	RUNTIME_PROFILES_PATH,
	RUNTIME_PROVISION_COMPATIBLE_PATH,
	RUNTIME_PROVISION_PATH,
	RUNTIME_PROVISION_PLAN_PATH,
	RUNTIME_READINESS_PATH,
	RUNTIME_RECOMMENDATION_PROFILES_PATH,
	RUNTIME_RECOMMENDATIONS_PATH,
	RUNTIME_SELECTION_PATH,
	runtimeCapabilitySelectionViewPath,
	runtimeEngineCompatibilityPath,
	runtimeEngineInstallPath,
	runtimeEngineProvisionPath,
	runtimeEngineRemovePath,
	runtimeEngineRepairPath,
	runtimeEngineTestPath,
	runtimeEngineUpdatePath,
	runtimeEngineValidatePath,
} from "@/config/api";
import type { HttpClient } from "@/services/http/HttpClient";
import type { RuntimeEngineAnalytics } from "./analyticsTypes";
import type {
	RuntimeEngineManagement,
	RuntimeSelectionUpdate,
} from "./managementTypes";
import type { RuntimeRepository } from "./RuntimeRepository";
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

export class HttpRuntimeRepository implements RuntimeRepository {
	private readonly httpClient: HttpClient;

	constructor(httpClient: HttpClient) {
		this.httpClient = httpClient;
	}

	getOverview(): Promise<RuntimeOverview> {
		return this.httpClient.get<RuntimeOverview>(RUNTIME_PATH);
	}

	getReadiness(): Promise<RuntimeReadiness> {
		return this.httpClient.get<RuntimeReadiness>(RUNTIME_READINESS_PATH);
	}

	getHealth(): Promise<RuntimeHealth> {
		return this.httpClient.get<RuntimeHealth>(RUNTIME_HEALTH_PATH);
	}

	listEngines(): Promise<RuntimeEngine[]> {
		return this.httpClient
			.get<{ engines: RuntimeEngine[] }>(RUNTIME_ENGINES_PATH)
			.then((response) => response.engines);
	}

	getCatalog(): Promise<RuntimeCatalog> {
		return this.httpClient.get<RuntimeCatalog>(RUNTIME_CATALOG_PATH);
	}

	getRecommendations(): Promise<RuntimeRecommendation[]> {
		return this.httpClient
			.get<{ recommendations: RuntimeRecommendation[] }>(
				RUNTIME_RECOMMENDATIONS_PATH,
			)
			.then((response) => response.recommendations);
	}

	listProfiles(): Promise<RuntimeProfile[]> {
		return this.httpClient
			.get<{ profiles: RuntimeProfile[] }>(RUNTIME_PROFILES_PATH)
			.then((response) => response.profiles);
	}

	testEngine(engineId: string): Promise<RuntimeEngineTestResult> {
		return this.httpClient.post<RuntimeEngineTestResult>(
			runtimeEngineTestPath(engineId),
			{},
		);
	}

	provisionEngine(engineId: string): Promise<Record<string, unknown>> {
		return this.httpClient.post<Record<string, unknown>>(
			runtimeEngineProvisionPath(engineId),
			{},
		);
	}

	provisionAll(): Promise<Record<string, unknown>> {
		return this.httpClient.post<Record<string, unknown>>(
			RUNTIME_PROVISION_PATH,
			{},
		);
	}

	provisionCompatibleAll(): Promise<Record<string, unknown>> {
		return this.httpClient.post<Record<string, unknown>>(
			RUNTIME_PROVISION_COMPATIBLE_PATH,
			{},
		);
	}

	getProvisioningPlan(): Promise<Record<string, unknown>> {
		return this.httpClient.get<Record<string, unknown>>(
			RUNTIME_PROVISION_PLAN_PATH,
		);
	}

	runFullBenchmark(): Promise<Record<string, unknown>> {
		return this.httpClient.post<Record<string, unknown>>(
			RUNTIME_BENCHMARK_FULL_PATH,
			{},
		);
	}

	validatePipeline(): Promise<RuntimeValidationReport> {
		return this.httpClient.post<RuntimeValidationReport>(
			RUNTIME_PIPELINE_VALIDATE_PATH,
			{},
		);
	}

	getHardware(): Promise<RuntimeHardwareOverview> {
		return this.httpClient.get<RuntimeHardwareOverview>(RUNTIME_HARDWARE_PATH);
	}

	getCompatibility(): Promise<RuntimeCompatibilitySummary> {
		return this.httpClient.get<RuntimeCompatibilitySummary>(
			RUNTIME_COMPATIBILITY_PATH,
		);
	}

	getCapabilityMaturity(): Promise<RuntimeCapabilityMaturityOverview> {
		return this.httpClient.get<RuntimeCapabilityMaturityOverview>(
			RUNTIME_CAPABILITY_MATURITY_PATH,
		);
	}

	getDashboard(): Promise<RuntimeDashboard> {
		return this.httpClient.get<RuntimeDashboard>(RUNTIME_DASHBOARD_PATH);
	}

	getEngineCompatibility(
		engineId: string,
	): Promise<RuntimeEngineCompatibility> {
		return this.httpClient.get<RuntimeEngineCompatibility>(
			runtimeEngineCompatibilityPath(engineId),
		);
	}

	listEngineAnalytics(): Promise<RuntimeEngineAnalytics[]> {
		return this.httpClient
			.get<{ engines: RuntimeEngineAnalytics[] }>(
				RUNTIME_ANALYTICS_ENGINES_PATH,
			)
			.then((response) => response.engines);
	}

	getCapabilitySelectionView(
		capability: string,
	): Promise<RuntimeCapabilitySelectionView> {
		return this.httpClient.get<RuntimeCapabilitySelectionView>(
			runtimeCapabilitySelectionViewPath(capability),
		);
	}

	getSelection(): Promise<Record<string, unknown>> {
		return this.httpClient.get<Record<string, unknown>>(RUNTIME_SELECTION_PATH);
	}

	getEngineManagement(): Promise<RuntimeEngineManagement> {
		return this.httpClient.get<RuntimeEngineManagement>(
			RUNTIME_ENGINE_MANAGEMENT_PATH,
		);
	}

	updateSelection(
		payload: RuntimeSelectionUpdate,
	): Promise<Record<string, unknown>> {
		return this.httpClient.put<Record<string, unknown>>(
			RUNTIME_SELECTION_PATH,
			payload,
		);
	}

	installEngine(engineId: string): Promise<Record<string, unknown>> {
		return this.httpClient.post<Record<string, unknown>>(
			runtimeEngineInstallPath(engineId),
			{},
		);
	}

	updateEngine(engineId: string): Promise<Record<string, unknown>> {
		return this.httpClient.post<Record<string, unknown>>(
			runtimeEngineUpdatePath(engineId),
			{},
		);
	}

	repairEngine(engineId: string): Promise<Record<string, unknown>> {
		return this.httpClient.post<Record<string, unknown>>(
			runtimeEngineRepairPath(engineId),
			{},
		);
	}

	removeEngine(engineId: string): Promise<void> {
		return this.httpClient.delete(runtimeEngineRemovePath(engineId));
	}

	validateEngine(engineId: string): Promise<Record<string, unknown>> {
		return this.httpClient.post<Record<string, unknown>>(
			runtimeEngineValidatePath(engineId),
			{},
		);
	}

	listExecutions(): Promise<Array<Record<string, unknown>>> {
		return this.httpClient
			.get<{ executions: Array<Record<string, unknown>> }>(
				RUNTIME_EXECUTIONS_PATH,
			)
			.then((response) => response.executions ?? []);
	}

	getRecommendationProfiles(): Promise<Record<string, unknown>> {
		return this.httpClient.get<Record<string, unknown>>(
			RUNTIME_RECOMMENDATION_PROFILES_PATH,
		);
	}
}
