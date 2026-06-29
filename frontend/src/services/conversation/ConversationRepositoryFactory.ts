import { API_BASE_URL } from "@/config/api";
import { FEATURES } from "@/config/features";
import { HttpClient } from "@/services/http/HttpClient";
import type { ConversationRepository } from "./ConversationRepository";
import { HttpConversationRepository } from "./HttpConversationRepository";
import { MockConversationRepository } from "./MockConversationRepository";

export function createConversationRepository(): ConversationRepository {
	if (FEATURES.USE_MOCK) {
		return new MockConversationRepository();
	}

	const httpClient = new HttpClient(API_BASE_URL);
	return new HttpConversationRepository(httpClient);
}
