import { API_BASE_URL } from "@/config/api";
import { HttpClient } from "@/services/http/HttpClient";
import { HttpRuntimeRepository } from "./HttpRuntimeRepository";
import type { RuntimeRepository } from "./RuntimeRepository";

export function createRuntimeRepository(): RuntimeRepository {
	const httpClient = new HttpClient(API_BASE_URL);
	return new HttpRuntimeRepository(httpClient);
}
