import {
	createContext,
	useCallback,
	useContext,
	useMemo,
	useSyncExternalStore,
	type ReactNode,
} from "react";
import {
	activityLogStore,
	appendActivityLog,
	type ActivityLogEntry,
	type ActivityLogLevel,
} from "./activityLogStore";

interface ActivityLogContextValue {
	entries: ActivityLogEntry[];
	append: (
		message: string,
		level?: ActivityLogLevel,
		source?: string,
	) => ActivityLogEntry;
	clear: () => void;
}

const ActivityLogContext = createContext<ActivityLogContextValue | null>(null);

export function ActivityLogProvider({ children }: { children: ReactNode }) {
	const entries = useSyncExternalStore(
		(listener) => activityLogStore.subscribe(listener),
		() => activityLogStore.getEntries(),
		() => activityLogStore.getEntries(),
	);

	const append = useCallback(
		(message: string, level: ActivityLogLevel = "info", source?: string) =>
			appendActivityLog(message, level, source),
		[],
	);

	const clear = useCallback(() => {
		activityLogStore.clear();
	}, []);

	const value = useMemo(
		() => ({
			entries,
			append,
			clear,
		}),
		[entries, append, clear],
	);

	return (
		<ActivityLogContext.Provider value={value}>
			{children}
		</ActivityLogContext.Provider>
	);
}

export function useActivityLog(): ActivityLogContextValue {
	const context = useContext(ActivityLogContext);

	if (!context) {
		throw new Error("useActivityLog must be used within ActivityLogProvider");
	}

	return context;
}

export function useOptionalActivityLog(): ActivityLogContextValue | null {
	return useContext(ActivityLogContext);
}
