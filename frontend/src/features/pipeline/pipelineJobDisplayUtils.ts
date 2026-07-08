import type { PipelineJob } from "@/services/pipeline/jobTypes";

export function formatPipelineStartedAt(
	startedAt: string | null | undefined,
	locale: string,
): string | null {
	if (!startedAt) {
		return null;
	}

	const parsed = Date.parse(startedAt);

	if (Number.isNaN(parsed)) {
		return null;
	}

	return new Intl.DateTimeFormat(locale, {
		dateStyle: "short",
		timeStyle: "short",
	}).format(new Date(parsed));
}

export function estimateMinutesFromSeconds(seconds?: number | null): number | null {
	if (seconds == null || seconds <= 0) {
		return null;
	}

	return Math.ceil(seconds / 60);
}

export type PipelineTimingLabels = {
	startedAt: string;
	notStarted: string;
	estimatedDuration: string;
	remainingMinutes: string;
};

export function buildPipelineStageTimingLines(
	job: PipelineJob,
	labels: PipelineTimingLabels,
	locale: string,
): string[] {
	const lines: string[] = [];
	const startedLabel = formatPipelineStartedAt(job.startedAt, locale);

	if (startedLabel) {
		lines.push(labels.startedAt.replace("{{time}}", startedLabel));
	} else if (job.status === "queued" || job.status === "waiting_user_choice") {
		lines.push(labels.notStarted);
	}

	const durationMinutes = estimateMinutesFromSeconds(job.estimatedDurationSeconds);

	if (durationMinutes != null) {
		lines.push(
			labels.estimatedDuration.replace("{{minutes}}", String(durationMinutes)),
		);
	}

	const remainingMinutes = estimateMinutesFromSeconds(job.estimatedRemainingSeconds);

	if (
		remainingMinutes != null &&
		(job.status === "running" || job.status === "queued")
	) {
		lines.push(
			labels.remainingMinutes.replace("{{minutes}}", String(remainingMinutes)),
		);
	}

	return lines;
}
