/** Single entry point for Vite environment variables. */
export const env = {
	useMock: import.meta.env.VITE_USE_MOCK === "true",
	apiBaseUrl: import.meta.env.VITE_API_BASE_URL ?? "http://localhost:8000",
} as const;
