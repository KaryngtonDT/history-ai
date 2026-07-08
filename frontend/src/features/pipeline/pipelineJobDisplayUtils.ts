import type { PipelineJob } from "@/services/pipeline/jobTypes";

export function formatPipelineDateTime(
	value: string | null | undefined,
	locale: string,
): string | null {
	if (!value) {
		return null;
	}

	const parsed = Date.parse(value);

	if (Number.isNaN(parsed)) {
		return null;
	}

	return new Intl.DateTimeFormat(locale, {
		dateStyle: "short",
		timeStyle: "short",
	}).format(new Date(parsed));
}

export function estimateMinutesFromSeconds(
	seconds?: number | null,
): number | null {
	if (seconds == null || seconds <= 0) {
		return null;
	}

	return Math.ceil(seconds / 60);
}

export function formatAccuracyPercent(value?: number | null): string | null {
	if (value == null) {
		return null;
	}

	return `${Math.round(value)}%`;
}

export type PipelineTimingLabels = {
	startedAt: string;
	notStarted: string;
	estimatedDuration: string;
	estimatedCompletion: string;
	actualCompletion: string;
	actualDuration: string;
	estimationAccuracy: string;
	elapsedTime: string;
	remainingMinutes: string;
	engine: string;
	hardwareProfile: string;
	currentStep: string;
};

export function buildPipelineStageTimingLines(
	job: PipelineJob,
	labels: PipelineTimingLabels,
	locale: string,
): string[] {
	const lines: string[] = [];
	const startedLabel = formatPipelineDateTime(job.startedAt, locale);

	if (startedLabel) {
		lines.push(labels.startedAt.replace("{{time}}", startedLabel));
	} else if (job.status === "queued" || job.status === "waiting_user_choice") {
		lines.push(labels.notStarted);
	}

	const durationMinutes = estimateMinutesFromSeconds(
		job.estimatedDurationSeconds,
	);

	if (durationMinutes != null) {
		lines.push(
			labels.estimatedDuration.replace("{{minutes}}", String(durationMinutes)),
		);
	}

	const estimatedCompletion = formatPipelineDateTime(
		job.estimatedCompletionAt,
		locale,
	);

	if (estimatedCompletion) {
		lines.push(
			labels.estimatedCompletion.replace("{{time}}", estimatedCompletion),
		);
	}

	if (job.status === "running" || job.status === "queued") {
		const elapsedMinutes = estimateMinutesFromSeconds(job.elapsedSeconds);

		if (elapsedMinutes != null) {
			lines.push(
				labels.elapsedTime.replace("{{minutes}}", String(elapsedMinutes)),
			);
		}

		const remainingMinutes = estimateMinutesFromSeconds(
			job.estimatedRemainingSeconds,
		);

		if (remainingMinutes != null) {
			lines.push(
				labels.remainingMinutes.replace(
					"{{minutes}}",
					String(remainingMinutes),
				),
			);
		}
	}

	if (
		job.status === "completed" ||
		job.status === "waiting_user_confirmation" ||
		job.status === "failed" ||
		job.status === "cancelled"
	) {
		const actualCompletion = formatPipelineDateTime(job.completedAt, locale);

		if (actualCompletion) {
			lines.push(labels.actualCompletion.replace("{{time}}", actualCompletion));
		}

		const actualMinutes = estimateMinutesFromSeconds(job.actualDurationSeconds);

		if (actualMinutes != null) {
			lines.push(
				labels.actualDuration.replace("{{minutes}}", String(actualMinutes)),
			);
		}

		const accuracy = formatAccuracyPercent(job.estimationAccuracyPercent);

		if (accuracy) {
			lines.push(labels.estimationAccuracy.replace("{{percent}}", accuracy));
		}
	}

	if (job.engineId || job.currentEngine || job.provider) {
		lines.push(
			labels.engine.replace(
				"{{engine}}",
				job.engineId ?? job.currentEngine ?? job.provider ?? "unknown",
			),
		);
	}

	if (job.hardwareProfile) {
		lines.push(
			labels.hardwareProfile.replace("{{profile}}", job.hardwareProfile),
		);
	}

	if (job.currentStep && job.status === "running") {
		lines.push(labels.currentStep.replace("{{step}}", job.currentStep));
	}

	return lines;
}
