import { describe, expect, it, vi } from "vitest";
import type { HttpClient } from "@/services/http/HttpClient";
import { AIEngineService } from "./AIEngineService";
import { HttpAIEngineRepository } from "./HttpAIEngineRepository";

describe("AIEngineService", () => {
	it("loads engines from repository", async () => {
		const listEngines = vi.fn().mockResolvedValue([
			{
				engineId: "speech-to-text",
				capability: "speech_to_text",
				enabled: true,
				providers: [],
			},
		]);

		const service = new AIEngineService({ listEngines });

		const engines = await service.listEngines();

		expect(listEngines).toHaveBeenCalled();
		expect(engines[0]?.engineId).toBe("speech-to-text");
	});
});

describe("HttpAIEngineRepository", () => {
	it("maps providers response", async () => {
		const get = vi.fn().mockResolvedValue({
			engines: [
				{
					engineId: "translation",
					capability: "translation",
					enabled: true,
					providers: [
						{
							providerId: "ollama",
							displayName: "Ollama",
							capability: "translation",
							enabled: true,
						},
					],
				},
			],
		});
		const httpClient = { get, post: vi.fn() } as unknown as HttpClient;
		const repository = new HttpAIEngineRepository(httpClient);

		const engines = await repository.listEngines();

		expect(get).toHaveBeenCalledWith("/api/ai/providers");
		expect(engines[0]?.providers[0]?.displayName).toBe("Ollama");
	});
});
