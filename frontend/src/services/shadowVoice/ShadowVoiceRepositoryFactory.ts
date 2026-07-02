import { HttpShadowVoiceRepository } from "./HttpShadowVoiceRepository";
import { MockShadowVoiceRepository } from "./MockShadowVoiceRepository";
import type { ShadowVoiceRepository } from "./ShadowVoiceRepository";

export function createShadowVoiceRepository(): ShadowVoiceRepository {
	const useMock = import.meta.env.VITE_USE_MOCK === "true";

	return useMock
		? new MockShadowVoiceRepository()
		: new HttpShadowVoiceRepository();
}
