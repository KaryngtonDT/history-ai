export type ActivityLogLevel = "info" | "warn" | "error";

export interface ActivityLogEntry {
	id: string;
	time: string;
	message: string;
	level: ActivityLogLevel;
	source?: string;
}

type ActivityLogListener = () => void;

const MAX_ENTRIES = 100;

function formatTime(date: Date): string {
	return date.toLocaleTimeString();
}

function createEntryId(): string {
	return `${Date.now()}-${Math.random().toString(36).slice(2, 8)}`;
}

class ActivityLogStore {
	private entries: ActivityLogEntry[] = [];
	private listeners = new Set<ActivityLogListener>();

	append(
		message: string,
		level: ActivityLogLevel = "info",
		source?: string,
	): ActivityLogEntry {
		const entry: ActivityLogEntry = {
			id: createEntryId(),
			time: formatTime(new Date()),
			message,
			level,
			source,
		};

		this.entries = [...this.entries.slice(-(MAX_ENTRIES - 1)), entry];
		this.notify();

		const prefix = source ? `[${source}]` : "[Activity]";
		const line = `${prefix} ${message}`;

		if (level === "error") {
			console.error(line);
		} else if (level === "warn") {
			console.warn(line);
		} else {
			console.info(line);
		}

		return entry;
	}

	getEntries(): ActivityLogEntry[] {
		return [...this.entries];
	}

	clear(): void {
		this.entries = [];
		this.notify();
	}

	subscribe(listener: ActivityLogListener): () => void {
		this.listeners.add(listener);

		return () => {
			this.listeners.delete(listener);
		};
	}

	private notify(): void {
		for (const listener of this.listeners) {
			listener();
		}
	}
}

export const activityLogStore = new ActivityLogStore();

export function appendActivityLog(
	message: string,
	level: ActivityLogLevel = "info",
	source?: string,
): ActivityLogEntry {
	return activityLogStore.append(message, level, source);
}
