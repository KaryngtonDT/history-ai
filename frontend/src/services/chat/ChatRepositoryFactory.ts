import { API_BASE_URL } from "@/config/api";
import { FEATURES } from "@/config/features";
import { HttpClient } from "@/services/http/HttpClient";
import type { ChatRepository } from "./ChatRepository";
import { HttpChatRepository } from "./HttpChatRepository";
import { MockChatRepository } from "./MockChatRepository";

export function createChatRepository(): ChatRepository {
	if (FEATURES.USE_MOCK) {
		return new MockChatRepository();
	}

	const httpClient = new HttpClient(API_BASE_URL);
	return new HttpChatRepository(httpClient);
}
