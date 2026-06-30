import { AI_PROVIDERS_PATH } from "@/config/api";
import type { HttpClient } from "@/services/http/HttpClient";
import type { AIEngineRepository } from "./AIEngineRepository";
import {
	type AIEngine,
	type AIProvidersResponse,
	mapAIEngineCapability,
} from "./types";

export class HttpAIEngineRepository implements AIEngineRepository {
	private readonly httpClient: HttpClient;

	constructor(httpClient: HttpClient) {
		this.httpClient = httpClient;
	}

	async listEngines(): Promise<AIEngine[]> {
		const response =
			await this.httpClient.get<AIProvidersResponse>(AI_PROVIDERS_PATH);

		return response.engines.map((engine) => ({
			engineId: engine.engineId,
			capability: mapAIEngineCapability(engine.capability),
			enabled: engine.enabled,
			providers: engine.providers.map((provider) => ({
				providerId: provider.providerId,
				displayName: provider.displayName,
				capability: mapAIEngineCapability(provider.capability),
				enabled: provider.enabled,
			})),
		}));
	}
}
