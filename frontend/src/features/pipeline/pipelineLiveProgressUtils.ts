import type { PipelineJob } from "@/services/pipeline/jobTypes";

export type PipelineJobWithClock = PipelineJob & {
	receivedAtMs?: number;
};

export function computeServerClockOffset(
	serverNow: string,
	clientNowMs = Date.now(),
): number {
	const parsed = Date.parse(serverNow);

	if (Number.isNaN(parsed)) {
		return 0;
	}

	return parsed - clientNowMs;
}

export function resolveEffectiveServerNowMs(
	job: PipelineJobWithClock,
	clientNowMs: number,
): number {
	if (job.serverNow && job.receivedAtMs != null) {
		const serverAtReceive = Date.parse(job.serverNow);

		if (!Number.isNaN(serverAtReceive)) {
			return serverAtReceive + (clientNowMs - job.receivedAtMs);
		}
	}

	return clientNowMs;
}

export function computeLiveElapsedSeconds(
	job: PipelineJobWithClock,
	clientNowMs: number,
): number | null {
	if (!job.startedAt) {
		return job.elapsedSeconds ?? null;
	}

	const startedMs = Date.parse(job.startedAt);

	if (Number.isNaN(startedMs)) {
		return job.elapsedSeconds ?? null;
	}

	const serverNowMs = resolveEffectiveServerNowMs(job, clientNowMs);

	return Math.max(0, Math.floor((serverNowMs - startedMs) / 1000));
}

export function computeLiveRemainingSeconds(
	job: PipelineJobWithClock,
	elapsedSeconds: number | null,
	progressPercent: number,
): number | null {
	if (elapsedSeconds == null) {
		return job.estimatedRemainingSeconds ?? null;
	}

	if (progressPercent >= 5 && progressPercent < 99) {
		const projectedTotal = Math.round(elapsedSeconds / (progressPercent / 100));

		return Math.max(0, projectedTotal - elapsedSeconds);
	}

	if (
		job.estimatedDurationSeconds != null &&
		job.estimatedDurationSeconds > 0
	) {
		return Math.max(0, job.estimatedDurationSeconds - elapsedSeconds);
	}

	return job.estimatedRemainingSeconds ?? null;
}

export function computeLiveCompletionAt(
	job: PipelineJobWithClock,
	remainingSeconds: number | null,
	clientNowMs: number,
): string | null {
	if (remainingSeconds == null || remainingSeconds <= 0) {
		return job.estimatedCompletionAt ?? null;
	}

	const serverNowMs = resolveEffectiveServerNowMs(job, clientNowMs);

	return new Date(serverNowMs + remainingSeconds * 1000).toISOString();
}

export function applyLiveProgressTick(
	job: PipelineJobWithClock,
	clientNowMs = Date.now(),
): PipelineJobWithClock {
	if (!job.isLive || job.liveFrozen) {
		return job;
	}

	const elapsedSeconds = computeLiveElapsedSeconds(job, clientNowMs);
	const progressPercent = job.progressPercent;
	const estimatedRemainingSeconds = computeLiveRemainingSeconds(
		job,
		elapsedSeconds,
		progressPercent,
	);
	const estimatedCompletionAt = computeLiveCompletionAt(
		job,
		estimatedRemainingSeconds,
		clientNowMs,
	);

	return {
		...job,
		elapsedSeconds,
		estimatedRemainingSeconds,
		estimatedCompletionAt,
	};
}

export function attachPipelineJobClock(
	job: PipelineJob,
	receivedAtMs = Date.now(),
): PipelineJobWithClock {
	if (!job.serverNow) {
		return { ...job, receivedAtMs };
	}

	return {
		...job,
		receivedAtMs,
	};
}

export function formatDurationClock(seconds?: number | null): string | null {
	if (seconds == null || seconds < 0) {
		return null;
	}

	const total = Math.floor(seconds);
	const hours = Math.floor(total / 3600);
	const minutes = Math.floor((total % 3600) / 60);
	const secs = total % 60;

	if (hours > 0) {
		return `${String(hours).padStart(2, "0")}:${String(minutes).padStart(2, "0")}:${String(secs).padStart(2, "0")}`;
	}

	return `${String(minutes).padStart(2, "0")}:${String(secs).padStart(2, "0")}`;
}

export function formatProcessingSpeedRatio(
	ratio?: number | null,
): string | null {
	if (ratio == null || ratio <= 0) {
		return null;
	}

	return `${ratio.toFixed(1)}× real-time`;
}

export function resolveHardwareProfileDisplay(job: PipelineJob): string | null {
	return (
		job.hardwareProfileCode ??
		job.hardwareProfileLabel ??
		job.hardwareProfile ??
		null
	);
}

export function hasRunningPipelineJobs(
	jobs: PipelineJob[] | undefined | null,
): boolean {
	return (jobs ?? []).some(
		(job) => job.status === "running" || job.status === "queued",
	);
}

export const LIVE_PIPELINE_POLL_MS = 1000;
export const IDLE_PIPELINE_POLL_MS = 5000;
export const LIVE_PIPELINE_TICK_MS = 1000;
