import {
	RUNTIME_BENCHMARK_FULL_PATH,
	RUNTIME_CATALOG_PATH,
	RUNTIME_ENGINES_PATH,
	RUNTIME_HEALTH_PATH,
	RUNTIME_PATH,
	RUNTIME_PIPELINE_VALIDATE_PATH,
	RUNTIME_PROFILES_PATH,
	RUNTIME_READINESS_PATH,
	RUNTIME_RECOMMENDATIONS_PATH,
	runtimeEngineTestPath,
} from "@/config/api";
import type { HttpClient } from "@/services/http/HttpClient";
import type { RuntimeRepository } from "./RuntimeRepository";
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

export class HttpRuntimeRepository implements RuntimeRepository {
	constructor(private readonly httpClient: HttpClient) {}

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

	testEngine(engineId: string): Promise<Record<string, unknown>> {
		return this.httpClient.post<Record<string, unknown>>(
			runtimeEngineTestPath(engineId),
			{},
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
}
