import { API_BASE_URL } from "@/config/api";
import { FEATURES } from "@/config/features";
import { HttpClient } from "@/services/http/HttpClient";
import { HttpSchedulerRepository } from "./HttpSchedulerRepository";
import { MockSchedulerRepository } from "./MockSchedulerRepository";
import type { SchedulerRepository } from "./SchedulerRepository";

export function createSchedulerRepository(): SchedulerRepository {
	if (FEATURES.USE_MOCK) {
		return new MockSchedulerRepository();
	}

	return new HttpSchedulerRepository(new HttpClient(API_BASE_URL));
}
