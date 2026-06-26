import { env } from "./env";

export const FEATURES = {
	USE_MOCK: env.useMock,
} as const;
