import type { PipelineJob, PipelineSourceStatus } from "@/services/pipeline/jobTypes";

export function isJobWaitingForTranscriptChoice(job: PipelineJob): boolean {
	if (job.status === "waiting_user_choice") {
		return true;
	}

	return job.status === "queued" && job.userChoiceRequired === true;
}

export function resolveJobsWaitingUserChoice(
	status: PipelineSourceStatus,
): PipelineJob[] {
	const fromBucket = status.jobsWaitingUserChoice ?? [];
	const fromActive = (status.activeJobs ?? []).filter(
		isJobWaitingForTranscriptChoice,
	);

	const seen = new Set<string>();
	const merged: PipelineJob[] = [];

	for (const job of [...fromBucket, ...fromActive]) {
		if (seen.has(job.jobId)) {
			continue;
		}

		seen.add(job.jobId);
		merged.push(job);
	}

	return merged;
}

export function isPipelineWaitingForTranscriptChoice(
	status: PipelineSourceStatus | null | undefined,
): boolean {
	if (!status) {
		return false;
	}

	return resolveJobsWaitingUserChoice(status).length > 0;
}

export function computeNonNegativeElapsedSeconds(
	startedAtMs: number | null,
	nowMs: number = Date.now(),
): number | null {
	if (startedAtMs == null) {
		return null;
	}

	return Math.max(0, Math.round((nowMs - startedAtMs) / 1000));
}

export function formatJobElapsedSeconds(job: PipelineJob): number | null {
	if (job.elapsedSeconds != null && Number.isFinite(job.elapsedSeconds)) {
		return Math.max(0, Math.round(job.elapsedSeconds));
	}

	if (!job.startedAt) {
		return null;
	}

	const startedAtMs = Date.parse(job.startedAt);

	if (Number.isNaN(startedAtMs)) {
		return null;
	}

	return computeNonNegativeElapsedSeconds(startedAtMs);
}
