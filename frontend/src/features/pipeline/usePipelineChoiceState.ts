import { useCallback, useEffect, useState } from "react";
import type { PipelineSourceStatus } from "@/services/pipeline/jobTypes";
import { pipelineJobService } from "@/services/pipeline/PipelineJobService";
import {
	isPipelineWaitingForTranscriptChoice,
	resolveJobsWaitingUserChoice,
} from "./pipelineChoiceUtils";

export function usePipelineChoiceState(sourceId: string | null, pollMs = 5000) {
	const [status, setStatus] = useState<PipelineSourceStatus | null>(null);
	const [loading, setLoading] = useState(true);
	const [error, setError] = useState<string | null>(null);

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
		} catch {
			setError("load_failed");
		} finally {
			setLoading(false);
		}
	}, [sourceId]);

	useEffect(() => {
		setLoading(true);
		void refresh();
	}, [refresh]);

	useEffect(() => {
		if (!sourceId) {
			return;
		}

		const waiting = isPipelineWaitingForTranscriptChoice(status);

		if (waiting) {
			return;
		}

		const timer = window.setInterval(() => {
			void refresh();
		}, pollMs);

		return () => window.clearInterval(timer);
	}, [pollMs, refresh, sourceId, status]);

	const waitingChoiceJobs = status ? resolveJobsWaitingUserChoice(status) : [];

	return {
		status,
		loading,
		error,
		refresh,
		waitingChoiceJobs,
		isWaitingForChoice: waitingChoiceJobs.length > 0,
	};
}
