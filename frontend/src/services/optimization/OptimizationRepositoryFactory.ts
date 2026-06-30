import { API_BASE_URL } from "@/config/api";
import { FEATURES } from "@/config/features";
import { HttpClient } from "@/services/http/HttpClient";
import { HttpOptimizationRepository } from "./HttpOptimizationRepository";
import { MockOptimizationRepository } from "./MockOptimizationRepository";
import type { OptimizationRepository } from "./OptimizationRepository";

export function createOptimizationRepository(): OptimizationRepository {
	if (FEATURES.USE_MOCK) {
		return new MockOptimizationRepository();
	}

	return new HttpOptimizationRepository(new HttpClient(API_BASE_URL));
}
