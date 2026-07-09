import {
	createContext,
	type ReactNode,
	useCallback,
	useContext,
	useEffect,
	useMemo,
	useState,
} from "react";
import {
	hasRunningPipelineJobs,
	IDLE_PIPELINE_POLL_MS,
	LIVE_PIPELINE_POLL_MS,
} from "@/features/pipeline/pipelineLiveProgressUtils";
import type { PipelineSourceStatus } from "@/services/pipeline/jobTypes";
import { pipelineJobService } from "@/services/pipeline/PipelineJobService";
import { isPipelineWaitingForTranscriptChoice } from "./pipelineChoiceUtils";

type PipelineSourceContextValue = {
	sourceId: string | null;
	status: PipelineSourceStatus | null;
	loading: boolean;
	error: string | null;
	refresh: () => Promise<void>;
	refreshToken: number;
};

const PipelineSourceContext = createContext<PipelineSourceContextValue | null>(
	null,
);

const EMPTY_PIPELINE_SOURCE_CONTEXT: PipelineSourceContextValue = {
	sourceId: null,
	status: null,
	loading: false,
	error: null,
	refresh: async () => {},
	refreshToken: 0,
};

export function PipelineSourceProvider({
	sourceId,
	children,
}: {
	sourceId: string | null;
	children: ReactNode;
}) {
	const [status, setStatus] = useState<PipelineSourceStatus | null>(null);
	const [loading, setLoading] = useState(Boolean(sourceId));
	const [error, setError] = useState<string | null>(null);
	const [refreshToken, setRefreshToken] = useState(0);

	const refresh = useCallback(async () => {
		if (!sourceId) {
			setStatus(null);
			setLoading(false);
			return;
		}

		try {
			const next = await pipelineJobService.loadStatus(sourceId);
			setStatus(next);
			setError(null);
			setRefreshToken((current) => current + 1);
		} catch {
			setError("Failed to load pipeline status.");
		} finally {
			setLoading(false);
		}
	}, [sourceId]);

	useEffect(() => {
		setLoading(Boolean(sourceId));
		void refresh();
	}, [refresh, sourceId]);

	const pollMs = useMemo(() => {
		if (!status || isPipelineWaitingForTranscriptChoice(status)) {
			return IDLE_PIPELINE_POLL_MS;
		}

		return hasRunningPipelineJobs([
			...(status.activeJobs ?? []),
			...(status.jobsWaitingConfirmation ?? []),
		])
			? LIVE_PIPELINE_POLL_MS
			: IDLE_PIPELINE_POLL_MS;
	}, [status]);

	useEffect(() => {
		if (!sourceId || isPipelineWaitingForTranscriptChoice(status)) {
			return;
		}

		const timer = window.setInterval(() => {
			void refresh();
		}, pollMs);

		return () => window.clearInterval(timer);
	}, [pollMs, refresh, sourceId, status]);

	const value = useMemo(
		() => ({
			sourceId,
			status,
			loading,
			error,
			refresh,
			refreshToken,
		}),
		[error, loading, refresh, refreshToken, sourceId, status],
	);

	return (
		<PipelineSourceContext.Provider value={value}>
			{children}
		</PipelineSourceContext.Provider>
	);
}

export function usePipelineSourceContext(): PipelineSourceContextValue {
	const context = useContext(PipelineSourceContext);

	if (!context) {
		return EMPTY_PIPELINE_SOURCE_CONTEXT;
	}

	return context;
}
