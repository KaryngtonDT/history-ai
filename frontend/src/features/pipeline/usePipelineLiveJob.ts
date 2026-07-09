import { useEffect, useMemo, useState } from "react";
import type { PipelineJobWithClock } from "@/features/pipeline/pipelineLiveProgressUtils";
import {
	applyLiveProgressTick,
	LIVE_PIPELINE_TICK_MS,
} from "@/features/pipeline/pipelineLiveProgressUtils";

export function usePipelineLiveJob(
	job: PipelineJobWithClock,
): PipelineJobWithClock {
	const [nowMs, setNowMs] = useState(() => Date.now());

	useEffect(() => {
		if (!job.isLive || job.liveFrozen) {
			return;
		}

		const timer = window.setInterval(() => {
			setNowMs(Date.now());
		}, LIVE_PIPELINE_TICK_MS);

		return () => window.clearInterval(timer);
	}, [job.isLive, job.liveFrozen]);

	return useMemo(() => applyLiveProgressTick(job, nowMs), [job, nowMs]);
}
