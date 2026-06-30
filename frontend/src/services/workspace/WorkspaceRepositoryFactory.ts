import { API_BASE_URL } from "@/config/api";
import { FEATURES } from "@/config/features";
import { HttpClient } from "@/services/http/HttpClient";
import { HttpWorkspaceRepository } from "./HttpWorkspaceRepository";
import { MockWorkspaceRepository } from "./MockWorkspaceRepository";
import type { WorkspaceRepository } from "./WorkspaceRepository";

export function createWorkspaceRepository(): WorkspaceRepository {
	if (FEATURES.USE_MOCK) {
		return new MockWorkspaceRepository();
	}

	return new HttpWorkspaceRepository(new HttpClient(API_BASE_URL));
}
