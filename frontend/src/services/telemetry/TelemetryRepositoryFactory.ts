import { API_BASE_URL } from "@/config/api";
import { FEATURES } from "@/config/features";
import { HttpClient } from "@/services/http/HttpClient";
import { HttpTelemetryRepository } from "./HttpTelemetryRepository";
import { MockTelemetryRepository } from "./MockTelemetryRepository";
import type { TelemetryRepository } from "./TelemetryRepository";

export function createTelemetryRepository(): TelemetryRepository {
	if (FEATURES.USE_MOCK) {
		return new MockTelemetryRepository();
	}

	return new HttpTelemetryRepository(new HttpClient(API_BASE_URL));
}
