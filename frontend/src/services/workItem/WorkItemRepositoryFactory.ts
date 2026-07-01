import { FEATURES } from "@/config/features";
import { HttpWorkItemRepository } from "./HttpWorkItemRepository";
import { MockWorkItemRepository } from "./MockWorkItemRepository";
import type { WorkItemRepository } from "./WorkItemRepository";

export function createWorkItemRepository(): WorkItemRepository {
	if (FEATURES.USE_MOCK) {
		return new MockWorkItemRepository();
	}

	return new HttpWorkItemRepository();
}
