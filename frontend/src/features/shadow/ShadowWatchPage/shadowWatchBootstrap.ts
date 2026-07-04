import { ApiError } from "@/shared/errors/ApiError";
import type {
	BootstrapCheckItem,
	BootstrapLogEntry,
} from "./ShadowWatchBootstrapPanel";

const LOG_PREFIX = "[ShadowWatch]";

export function logBootstrap(message: string, level: BootstrapLogEntry["level"] = "info"): void {
	const line = `${LOG_PREFIX} ${message}`;
	if (level === "error") {
		console.error(line);
		return;
	}

	if (level === "warn") {
		console.warn(line);
		return;
	}

	console.info(line);
}

export function formatBootstrapError(error: unknown): string {
	if (error instanceof ApiError) {
		return `API ${error.status} — ${error.message}`;
	}

	if (error instanceof Error) {
		return error.message;
	}

	return String(error);
}

export function appendBootstrapLog(
	entries: BootstrapLogEntry[],
	message: string,
	level: BootstrapLogEntry["level"] = "info",
): BootstrapLogEntry[] {
	logBootstrap(message, level);
	const time = new Date().toLocaleTimeString();
	return [...entries.slice(-19), { time, message, level }];
}

export function updateBootstrapCheck(
	checks: BootstrapCheckItem[],
	id: string,
	patch: Partial<Omit<BootstrapCheckItem, "id">>,
): BootstrapCheckItem[] {
	return checks.map((item) => (item.id === id ? { ...item, ...patch } : item));
}
