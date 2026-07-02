import { HttpShadowIdentityRepository } from "./HttpShadowIdentityRepository";
import { MockShadowIdentityRepository } from "./MockShadowIdentityRepository";
import type { ShadowIdentityRepository } from "./ShadowIdentityRepository";

export function createShadowIdentityRepository(): ShadowIdentityRepository {
	return import.meta.env.VITE_USE_MOCK === "true"
		? new MockShadowIdentityRepository()
		: new HttpShadowIdentityRepository();
}
